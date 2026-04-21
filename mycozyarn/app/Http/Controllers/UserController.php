<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile()
    {
        return view('user.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $user->fill($request->validated());
        $user->save();

        return back()->with('cart_flash', 'Cập nhật thông tin thành công.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|max:100|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required'     => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'          => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed'    => 'Mật khẩu mới nhập lại không khớp.',
        ]);

        $user = Auth::user();
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = $data['new_password']; // auto-hash via User model cast
        $user->save();

        return back()->with('cart_flash', 'Đổi mật khẩu thành công.');
    }

    public function orders()
    {
        $userId    = Auth::id();
        $allOrders = session('orders', []);

        // Chỉ show đơn của user hiện tại, sắp xếp mới → cũ
        $orders = array_filter($allOrders, fn($o) => ($o['user_id'] ?? null) === $userId);
        uasort($orders, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return view('user.account.orders', [
            'orders' => $orders,
        ]);
    }

    public function orderShow(string $id)
    {
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;

        abort_unless($order, 404);
        abort_unless(($order['user_id'] ?? null) === Auth::id(), 403);

        // Build timeline + gom reviews của user cho các item trong đơn này
        $timeline = OrderTimeline::compute($order);

        $allReviews = session('reviews', []);
        $itemReviews = [];
        foreach ($order['items'] as $item) {
            $itemKey = $item['key'] ?? null;
            if (!$itemKey) continue;
            $reviewKey = $order['id'] . '::' . $itemKey;
            if (isset($allReviews[$reviewKey])) {
                $itemReviews[$itemKey] = $allReviews[$reviewKey];
            }
        }

        return view('user.account.order-detail', [
            'order'        => $order,
            'timeline'     => $timeline,
            'itemReviews'  => $itemReviews,
            'canReview'    => !$timeline['cancelled'] && OrderTimeline::currentKey($order) === 'delivered',
        ]);
    }
}
