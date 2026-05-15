<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Support\ClientPasswordNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Tầng business logic cho User. Controller chỉ gọi service này,
 * không truy vấn Eloquent trực tiếp.
 */
class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    /* ─────────────── Read ─────────────── */

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($filters, $perPage);
    }

    public function stats(): array
    {
        return $this->users->stats();
    }

    public function findById(int $id): ?User
    {
        return $this->users->findById($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->users->findByEmail($email);
    }

    /* ─────────────── Write ─────────────── */

    /**
     * Đăng ký / admin tạo user. Email hạ cấp + trim, default role/status.
     */
    public function create(array $data): User
    {
        $payload = [
            'name'     => trim((string) ($data['name'] ?? '')),
            'email'    => strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => (string) ($data['password'] ?? ''),
            'phone'    => $data['phone']   ?? null,
            'address'  => $data['address'] ?? null,
            'avatar'   => $data['avatar']  ?? null,
            'role'     => $data['role']    ?? 'user',
            'status'   => $data['status']  ?? 'active',
        ];

        return $this->users->create($payload);
    }

    /**
     * User tự cập nhật profile của mình. Không cho đổi role/status/email/password ở đây.
     */
    public function updateProfile(User $user, array $data): User
    {
        unset($data['role'], $data['status'], $data['email'], $data['password']);
        return $this->users->update($user, $data);
    }

    /**
     * Admin cập nhật user khác. Có thể đổi role/status/email; password đổi qua flow riêng.
     */
    public function adminUpdate(User $user, array $data): User
    {
        if (!empty($data['email'])) {
            $data['email'] = strtolower(trim((string) $data['email']));
        }
        unset($data['password']);
        return $this->users->update($user, $data);
    }

    /**
     * Đổi mật khẩu (yêu cầu nhập lại pass cũ). Throw ValidationException nếu sai pass cũ.
     */
    public function changePassword(User $user, string $currentPlain, string $newPlain): void
    {
        // Client băm SHA-256(password) trước khi gửi; chuẩn hoá hai chiều để
        // Hash::check khớp với bcrypt(SHA256(plaintext)) đã lưu trong DB.
        $currentNormalized = ClientPasswordNormalizer::normalize($currentPlain);

        if (!Hash::check($currentNormalized, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }
        // Mutator của User model sẽ tự normalize + bcrypt
        $user->password = $newPlain;
        $user->save();
    }

    /**
     * Đặt lại mật khẩu theo email (dùng cho luồng quên mật khẩu — không yêu cầu pass cũ).
     */
    public function resetPasswordByEmail(string $email, string $newPlain): User
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            throw new RuntimeException('Không tìm thấy tài khoản.');
        }
        $user->password = $newPlain;
        $user->save();
        return $user;
    }

    /**
     * Xoá user. Cấm xoá chính mình (so sánh với $actorId).
     */
    public function delete(User $user, ?int $actorId = null): void
    {
        if ($actorId !== null && $user->id === $actorId) {
            throw new RuntimeException('Không thể xoá chính tài khoản đang đăng nhập.');
        }
        $this->users->delete($user);
    }

    /**
     * Khoá / mở khoá user. Cấm tự khoá chính mình.
     */
    public function toggleBlock(User $user, ?int $actorId = null): User
    {
        if ($actorId !== null && $user->id === $actorId) {
            throw new RuntimeException('Không thể thay đổi trạng thái của chính bạn.');
        }
        return $this->users->toggleStatus($user);
    }

    /* ─────────────── Auth helpers ─────────────── */

    /**
     * True nếu tài khoản tồn tại và đang ở trạng thái active.
     */
    public function isActive(User $user): bool
    {
        return ($user->status ?? 'active') === 'active';
    }

    /* ─────────────── Risk score (admin/users/show) ─────────────── */

    /**
     * Tính chỉ số rủi ro cho user dựa trên các đơn hàng đã có.
     *
     * @param array<int, array<string,mixed>> $orders danh sách đơn (mỗi đơn có status, items, total…)
     * @return array{score:int, level:array{key:string,label:string}, reasons:array<int,string>, bucket:array<string,int>, totals:array{spent:int, items:int, cancelRatio:int, returnRatio:int, accountAgeDays:int}}
     */
    public function computeRisk(User $user, array $orders): array
    {
        $bucket = ['active' => 0, 'received' => 0, 'cancelled' => 0, 'return_requested' => 0, 'returned' => 0];
        $totalSpent = 0;
        $totalItems = 0;

        foreach ($orders as $o) {
            $s = $o['status'] ?? 'pending';
            if (\in_array($s, ['cancelled', 'returned', 'return_requested', 'received'], true)) {
                $bucket[$s] = ($bucket[$s] ?? 0) + 1;
            } else {
                $bucket['active']++;
            }
            if (!\in_array($s, ['cancelled', 'returned'], true)) {
                $totalSpent += (int) ($o['total'] ?? 0);
                $totalItems += \count($o['items'] ?? []);
            }
        }

        $totalOrders = \count($orders);
        $cancelRatio = $totalOrders > 0 ? (int) round($bucket['cancelled'] / $totalOrders * 100) : 0;
        $returnRatio = $totalOrders > 0 ? (int) round(($bucket['returned'] + $bucket['return_requested']) / $totalOrders * 100) : 0;

        $reasons = [];
        $score = 0;
        $score += $bucket['cancelled']        * 10;
        $score += $bucket['return_requested'] * 15;
        $score += $bucket['returned']         * 8;

        if ($cancelRatio >= 50 && $totalOrders >= 3) {
            $score += 20;
            $reasons[] = "Tỷ lệ huỷ đơn cao ({$cancelRatio}%)";
        }
        if ($returnRatio >= 40 && $totalOrders >= 3) {
            $score += 15;
            $reasons[] = "Tỷ lệ yêu cầu trả hàng cao ({$returnRatio}%)";
        }
        if ($bucket['cancelled'] >= 3) {
            $reasons[] = "Có {$bucket['cancelled']} đơn đã huỷ";
        }
        if ($bucket['return_requested'] >= 2) {
            $reasons[] = "Có {$bucket['return_requested']} yêu cầu trả hàng đang xử lý";
        }
        $accountAgeDays = (int) ($user->created_at?->diffInDays(now()) ?? 0);
        if ($accountAgeDays <= 3 && $totalOrders >= 5) {
            $score += 15;
            $reasons[] = "Tài khoản mới ({$accountAgeDays} ngày) nhưng có {$totalOrders} đơn";
        }
        if ($user->status === 'blocked') {
            $reasons[] = "Tài khoản hiện đang bị khoá";
        }

        $score = (int) min(100, $score);
        $level = match (true) {
            $score >= 80 => ['key' => 'critical', 'label' => 'Rất cao — nên khoá tài khoản'],
            $score >= 50 => ['key' => 'high',     'label' => 'Cao — cần theo dõi'],
            $score >= 20 => ['key' => 'medium',   'label' => 'Trung bình'],
            default      => ['key' => 'low',      'label' => 'Thấp — bình thường'],
        };

        return [
            'score'   => $score,
            'level'   => $level,
            'reasons' => $reasons,
            'bucket'  => $bucket,
            'totals'  => [
                'spent'          => $totalSpent,
                'items'          => $totalItems,
                'cancelRatio'    => $cancelRatio,
                'returnRatio'    => $returnRatio,
                'accountAgeDays' => $accountAgeDays,
            ],
        ];
    }
}
