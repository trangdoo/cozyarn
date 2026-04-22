<?php

namespace App\Http\Controllers\Admin;

use App\Support\Notifications;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index()
    {
        $all = session('notifications', []);
        usort($all, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $stats = [
            'all'   => \count($all),
            'order' => \count(array_filter($all, fn($n) => ($n['type'] ?? '') === 'order')),
            'promo' => \count(array_filter($all, fn($n) => ($n['type'] ?? '') === 'promo')),
        ];

        return view('admin.notifications.index', [
            'notifications' => array_values($all),
            'stats'         => $stats,
        ]);
    }

    public function create()
    {
        return view('admin.notifications.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'content'     => 'required|string|max:500',
            'link'        => 'nullable|string|max:300',
            'icon'        => 'required|in:promo-discount,promo-ship,promo-new',
            'valid_until' => 'nullable|string|max:50',
            'code'        => 'nullable|string|max:50',
        ]);

        // Push cho chính admin (demo). Khi có DB sẽ broadcast tất cả users.
        Notifications::push([
            'id'         => 'ADMIN-' . strtoupper(Str::random(8)),
            'type'       => 'promo',
            'title'      => $data['title'],
            'content'    => $data['content'],
            'link'       => $data['link'] ?? null,
            'icon'       => $data['icon'],
            'created_at' => now()->toDateTimeString(),
            'meta'       => [
                'valid_until' => $data['valid_until'] ?? 'Không giới hạn',
                'code'        => $data['code'] ?? null,
                'details'     => [$data['content']],
                'highlights'  => [],
                'cta'         => 'Xem ngay',
                'banner'      => null,
            ],
        ]);

        return redirect()->route('admin.notifications.index')->with('cart_flash', 'Đã gửi thông báo khuyến mãi. (Demo: chỉ push cho admin — khi có DB sẽ push all users)');
    }

    public function destroy(string $id)
    {
        $all = session('notifications', []);
        unset($all[$id]);
        session(['notifications' => $all]);
        return back()->with('cart_flash', 'Đã xoá thông báo.');
    }
}
