<?php

namespace App\Http\Controllers;

use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|string',
            'item_key' => 'required|string',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Vui lòng chọn số sao.',
            'rating.min'      => 'Chọn ít nhất 1 sao.',
        ]);

        $orders = session('orders', []);
        $order  = $orders[$data['order_id']] ?? null;
        abort_unless($order && ($order['user_id'] ?? null) === Auth::id(), 403);

        $stageKey = OrderTimeline::currentKey($order);
        if ($stageKey !== 'delivered') {
            return back()->withErrors(['rating' => 'Chỉ có thể đánh giá khi đơn đã giao thành công.']);
        }

        $item = null;
        foreach ($order['items'] as $i) {
            if (($i['key'] ?? null) === $data['item_key']) {
                $item = $i;
                break;
            }
        }
        abort_unless($item, 404, 'Không tìm thấy sản phẩm trong đơn này.');

        $reviews   = session('reviews', []);
        $reviewKey = $data['order_id'] . '::' . $data['item_key'];

        $reviews[$reviewKey] = [
            'id'               => $reviewKey,
            'user_id'          => Auth::id(),
            'order_id'         => $data['order_id'],
            'item_key'         => $data['item_key'],
            'product_category' => $item['category'],
            'product_slug'     => $item['slug'],
            'product_name'     => $item['name'],
            'product_image'    => $item['image'] ?? null,
            'variant'          => $item['variant'] ?? null,
            'size'             => $item['size'] ?? null,
            'rating'           => (int) $data['rating'],
            'comment'          => $data['comment'] ?? '',
            'created_at'       => now()->toDateTimeString(),
        ];
        session(['reviews' => $reviews]);

        return back()->with('cart_flash', 'Cảm ơn bạn đã đánh giá!');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['id' => 'required|string']);

        $reviews = session('reviews', []);
        $review  = $reviews[$data['id']] ?? null;
        abort_unless($review && ($review['user_id'] ?? null) === Auth::id(), 403);

        unset($reviews[$data['id']]);
        session(['reviews' => $reviews]);

        return back()->with('cart_flash', 'Đã xoá đánh giá.');
    }

    public function myReviews()
    {
        $userId = Auth::id();
        $all    = session('reviews', []);
        $mine   = array_filter($all, fn($r) => ($r['user_id'] ?? null) === $userId);
        uasort($mine, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return view('user.account.my-reviews', ['reviews' => $mine]);
    }
}
