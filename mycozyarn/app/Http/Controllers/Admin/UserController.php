<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $role   = $request->query('role', 'all');
        $status = $request->query('status', 'all');

        $query = User::query()->orderByDesc('created_at');
        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($role !== 'all')   $query->where('role', $role);
        if ($status !== 'all') $query->where('status', $status);

        return view('admin.users.index', [
            'users'  => $query->paginate(15)->withQueryString(),
            'filter' => compact('q', 'role', 'status'),
            'stats'  => [
                'total'   => User::count(),
                'admin'   => User::where('role', 'admin')->count(),
                'active'  => User::where('status', 'active')->count(),
                'blocked' => User::where('status', 'blocked')->count(),
            ],
        ]);
    }

    public function show(User $user)
    {
        // Lấy tất cả đơn hàng của user này trong session. Khi migrate DB sẽ đổi sang:
        // $orders = Order::where('user_id', $user->id)->latest()->get();
        $orders = array_values(array_filter(session('orders', []), fn($o) =>
            ($o['user_id'] ?? null) === $user->id
        ));
        usort($orders, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        // Phân loại theo trạng thái cuối
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
            // Chỉ tính vào "đã chi" khi đơn không bị huỷ/hoàn tiền
            if (!\in_array($s, ['cancelled', 'returned'], true)) {
                $totalSpent += (int) ($o['total'] ?? 0);
                $totalItems += \count($o['items'] ?? []);
            }
        }

        $totalOrders = \count($orders);
        $cancelRatio = $totalOrders > 0 ? round($bucket['cancelled'] / $totalOrders * 100) : 0;
        $returnRatio = $totalOrders > 0 ? round(($bucket['returned'] + $bucket['return_requested']) / $totalOrders * 100) : 0;

        // Risk score 0-100
        $risk = 0;
        $reasons = [];
        $risk += $bucket['cancelled']        * 10;
        $risk += $bucket['return_requested'] * 15;
        $risk += $bucket['returned']         * 8;
        if ($cancelRatio >= 50 && $totalOrders >= 3) {
            $risk += 20;
            $reasons[] = "Tỷ lệ huỷ đơn cao ({$cancelRatio}%)";
        }
        if ($returnRatio >= 40 && $totalOrders >= 3) {
            $risk += 15;
            $reasons[] = "Tỷ lệ yêu cầu trả hàng cao ({$returnRatio}%)";
        }
        if ($bucket['cancelled'] >= 3) {
            $reasons[] = "Có {$bucket['cancelled']} đơn đã huỷ";
        }
        if ($bucket['return_requested'] >= 2) {
            $reasons[] = "Có {$bucket['return_requested']} yêu cầu trả hàng đang xử lý";
        }
        // Tài khoản mới + nhiều đơn → cờ đáng ngờ
        $accountAgeDays = $user->created_at?->diffInDays(now()) ?? 0;
        if ($accountAgeDays <= 3 && $totalOrders >= 5) {
            $risk += 15;
            $reasons[] = "Tài khoản mới ({$accountAgeDays} ngày) nhưng có {$totalOrders} đơn";
        }
        if ($user->status === 'blocked') {
            $reasons[] = "Tài khoản hiện đang bị khoá";
        }
        $risk = min(100, $risk);

        $riskLevel = match (true) {
            $risk >= 80 => ['key' => 'critical', 'label' => 'Rất cao — nên khoá tài khoản'],
            $risk >= 50 => ['key' => 'high',     'label' => 'Cao — cần theo dõi'],
            $risk >= 20 => ['key' => 'medium',   'label' => 'Trung bình'],
            default     => ['key' => 'low',      'label' => 'Thấp — bình thường'],
        };

        return view('admin.users.show', [
            'user'        => $user,
            'orders'      => $orders,
            'stats'       => [
                'total'        => $totalOrders,
                'active'       => $bucket['active'],
                'received'     => $bucket['received'],
                'cancelled'    => $bucket['cancelled'],
                'returned'     => $bucket['returned'],
                'returnReq'    => $bucket['return_requested'],
                'cancelRatio'  => $cancelRatio,
                'returnRatio'  => $returnRatio,
                'totalSpent'   => $totalSpent,
                'totalItems'   => $totalItems,
                'accountAgeDays' => $accountAgeDays,
            ],
            'risk'        => $risk,
            'riskLevel'   => $riskLevel,
            'riskReasons' => $reasons,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => "required|email|max:150|unique:users,email,{$user->id}",
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:300',
            'role'    => 'required|in:user,admin',
            'status'  => 'required|in:active,blocked',
        ]);

        $user->update($data);
        return back()->with('cart_flash', 'Đã cập nhật tài khoản.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('cart_flash', 'Không thể xoá chính tài khoản đang đăng nhập.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('cart_flash', 'Đã xoá tài khoản.');
    }

    public function toggleBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('cart_flash', 'Không thể thay đổi trạng thái của chính bạn.');
        }
        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();
        return back()->with('cart_flash', $user->status === 'blocked' ? 'Đã khoá tài khoản.' : 'Đã mở khoá tài khoản.');
    }
}
