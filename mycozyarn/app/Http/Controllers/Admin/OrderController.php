<?php

namespace App\Http\Controllers\Admin;

use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Admin orders — đọc từ session('orders') của admin.
 * Ghi chú: session-based per-user, admin chỉ thấy đơn trong phiên hiện tại.
 * Khi migrate sang DB sẽ query bình thường qua Order model.
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $all = array_values(session('orders', []));
        usort($all, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');

        $filtered = array_filter($all, function ($o) use ($q, $status) {
            if ($q !== '' && !str_contains(mb_strtolower($o['id']), mb_strtolower($q))
                          && !str_contains(mb_strtolower($o['name'] ?? ''), mb_strtolower($q))) {
                return false;
            }
            if ($status !== 'all') {
                $stage = \in_array($o['status'] ?? '', ['cancelled', 'returned', 'return_requested', 'received'], true)
                    ? $o['status']
                    : OrderTimeline::currentKey($o);
                if ($stage !== $status) return false;
            }
            return true;
        });

        $stats = [
            'all'        => \count($all),
            'pending'    => \count(array_filter($all, fn($o) => OrderTimeline::currentKey($o) === 'pending')),
            'shipping'   => \count(array_filter($all, fn($o) => OrderTimeline::currentKey($o) === 'shipping')),
            'delivered'  => \count(array_filter($all, fn($o) => ($o['status'] ?? '') === 'received' || OrderTimeline::currentKey($o) === 'delivered')),
            'cancelled'  => \count(array_filter($all, fn($o) => ($o['status'] ?? '') === 'cancelled')),
            'return'     => \count(array_filter($all, fn($o) => \in_array($o['status'] ?? '', ['returned', 'return_requested'], true))),
        ];

        return view('admin.orders.index', [
            'orders' => array_values($filtered),
            'filter' => compact('q', 'status'),
            'stats'  => $stats,
        ]);
    }

    public function show(string $id)
    {
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;
        abort_unless($order, 404);

        $timeline = OrderTimeline::compute($order);

        return view('admin.orders.show', [
            'order'    => $order,
            'timeline' => $timeline,
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,delivered,received,cancelled,return_requested,returned',
        ]);

        $orders = session('orders', []);
        abort_unless(isset($orders[$id]), 404);

        $orders[$id]['status']          = $data['status'];
        $orders[$id]['admin_updated_at'] = now()->toDateTimeString();
        session(['orders' => $orders]);

        return back()->with('cart_flash', 'Đã cập nhật trạng thái đơn.');
    }
}
