<?php

namespace App\Http\Controllers;

use App\Support\Notifications;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        Notifications::syncForUser(Auth::id());
        $all = Notifications::forUser(Auth::id());

        $filter = $request->query('type', 'all'); // all | order | promo
        $filtered = match ($filter) {
            'order' => array_filter($all, fn($n) => ($n['type'] ?? '') === 'order'),
            'promo' => array_filter($all, fn($n) => ($n['type'] ?? '') === 'promo'),
            default => $all,
        };

        $counts = [
            'all'   => \count($all),
            'order' => \count(array_filter($all, fn($n) => ($n['type'] ?? '') === 'order')),
            'promo' => \count(array_filter($all, fn($n) => ($n['type'] ?? '') === 'promo')),
        ];

        return view('user.notifications.index', [
            'notifications' => $filtered,
            'activeFilter'  => $filter,
            'counts'        => $counts,
            'unreadCount'   => Notifications::unreadCount(Auth::id()),
        ]);
    }

    public function open(string $id)
    {
        $notif = Notifications::find($id, Auth::id());
        abort_unless($notif, 404);

        Notifications::markRead($id);

        // Thông báo khuyến mãi → render trang chi tiết với nội dung đầy đủ
        if (($notif['type'] ?? '') === 'promo') {
            return view('user.notifications.detail', [
                'notification' => $notif,
            ]);
        }

        // Thông báo đơn hàng (hoặc loại khác) → redirect đến link (vd: /don-hang/{id}#trang-thai)
        if (!empty($notif['link'])) {
            return redirect($notif['link']);
        }
        return redirect()->route('user.notifications.index');
    }

    public function markAllRead()
    {
        Notifications::markAllRead(Auth::id());
        return back()->with('cart_flash', 'Đã đánh dấu tất cả là đã đọc.');
    }
}
