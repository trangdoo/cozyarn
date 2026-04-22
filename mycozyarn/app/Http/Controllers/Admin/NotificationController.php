<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Support\BroadcastQueue;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    private const PAGE_SIZE = 15;

    public function index(Request $request)
    {
        $all = BroadcastQueue::all();
        // Sort mới nhất trước
        usort($all, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $q      = trim((string) $request->query('q', ''));
        $type   = $request->query('type', 'all');
        $status = $request->query('status', 'all'); // all | sent | scheduled

        $now = now()->toDateTimeString();
        $filtered = array_filter($all, function ($n) use ($q, $type, $status, $now) {
            if ($q !== '' && !str_contains(mb_strtolower(($n['title'] ?? '') . ' ' . ($n['content'] ?? '')), mb_strtolower($q))) {
                return false;
            }
            if ($type !== 'all' && ($n['type'] ?? 'promo') !== $type) return false;
            $isScheduled = ($n['send_at'] ?? $now) > $now;
            if ($status === 'sent' && $isScheduled) return false;
            if ($status === 'scheduled' && !$isScheduled) return false;
            return true;
        });

        $page  = max(1, (int) $request->query('page', 1));
        $items = \array_slice($filtered, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE);

        $paginator = new LengthAwarePaginator(
            $items,
            \count($filtered),
            self::PAGE_SIZE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total'     => \count($all),
            'sent'      => \count(array_filter($all, fn($n) => ($n['send_at'] ?? $now) <= $now)),
            'scheduled' => \count(array_filter($all, fn($n) => ($n['send_at'] ?? $now) > $now)),
        ];

        return view('admin.notifications.index', [
            'notifications' => $paginator,
            'filter'        => compact('q', 'type', 'status'),
            'stats'         => $stats,
        ]);
    }

    public function create()
    {
        return view('admin.notifications.form', [
            'notification' => null,
            'users'        => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function edit(string $id)
    {
        $notif = BroadcastQueue::find($id);
        abort_unless($notif, 404);
        // Không cho sửa nếu đã gửi
        if (($notif['send_at'] ?? now()->toDateTimeString()) <= now()->toDateTimeString()) {
            return redirect()->route('admin.notifications.index')
                ->with('cart_flash', 'Không thể sửa thông báo đã gửi.');
        }
        return view('admin.notifications.form', [
            'notification' => $notif,
            'users'        => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->saveBroadcast($data);

        $sendAt = $data['send_at'] ?? now()->toDateTimeString();
        $msg = $sendAt > now()->toDateTimeString()
            ? 'Đã lên lịch gửi thông báo.'
            : 'Đã gửi thông báo đến người nhận.';

        return redirect()->route('admin.notifications.index')->with('cart_flash', $msg);
    }

    public function update(Request $request, string $id)
    {
        $notif = BroadcastQueue::find($id);
        abort_unless($notif, 404);
        if (($notif['send_at'] ?? now()->toDateTimeString()) <= now()->toDateTimeString()) {
            return redirect()->route('admin.notifications.index')
                ->with('cart_flash', 'Không thể sửa thông báo đã gửi.');
        }

        $data = $this->validateData($request);
        $this->saveBroadcast($data, existingId: $id, existingCreated: $notif['created_at'] ?? null);
        return redirect()->route('admin.notifications.index')->with('cart_flash', 'Đã cập nhật thông báo.');
    }

    public function destroy(string $id)
    {
        BroadcastQueue::delete($id);
        return back()->with('cart_flash', 'Đã xoá thông báo.');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'string',
        ]);
        $n = BroadcastQueue::deleteMany($data['ids']);
        return back()->with('cart_flash', "Đã xoá {$n} thông báo.");
    }

    /* ═══════════════════════ helpers ═══════════════════════ */

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'           => 'required|string|max:200',
            'content'         => 'required|string|max:500',
            'type'            => 'required|in:promo,order',
            'icon'            => 'required|in:promo-discount,promo-ship,promo-new,order-placed,order-confirmed,order-shipping,order-delivered',
            'link'            => 'nullable|string|max:300',
            'code'            => 'nullable|string|max:50',
            'valid_until'     => 'nullable|string|max:50',
            'send_at'         => 'nullable|date',
            'recipient_mode'  => 'required|in:all,role_user,role_admin,specific',
            'recipient_users' => 'nullable|array',
            'recipient_users.*' => 'integer',
        ]);
    }

    private function saveBroadcast(array $data, ?string $existingId = null, ?string $existingCreated = null): void
    {
        $recipients = match ($data['recipient_mode']) {
            'all'         => 'all',
            'role_user'   => 'role:user',
            'role_admin'  => 'role:admin',
            'specific'    => array_values(array_map('intval', $data['recipient_users'] ?? [])),
        };

        $id = $existingId ?? 'BC-' . strtoupper(Str::random(8));

        BroadcastQueue::save([
            'id'           => $id,
            'type'         => $data['type'],
            'title'        => $data['title'],
            'content'      => $data['content'],
            'link'         => $data['link'] ?? null,
            'icon'         => $data['icon'],
            'recipients'   => $recipients,
            'send_at'      => $data['send_at'] ?? now()->toDateTimeString(),
            'created_at'   => $existingCreated ?? now()->toDateTimeString(),
            'sender_id'    => auth()->id(),
            'delivered_to' => [],
            'meta'         => [
                'code'        => $data['code'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'details'     => [$data['content']],
                'highlights'  => [],
                'cta'         => 'Xem ngay',
                'banner'      => null,
            ],
        ]);
    }
}
