<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Support\Notifications;
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
        return view('user.account.orders', [
            'orders' => $this->filterUserOrders(fn($o) => !\in_array($o['status'] ?? '', ['cancelled', 'returned', 'return_requested', 'received'], true)),
            'activeTab' => 'active',
        ]);
    }

    public function completedOrders()
    {
        return view('user.account.orders', [
            'orders' => $this->filterUserOrders(fn($o) => ($o['status'] ?? '') === 'received'),
            'activeTab' => 'completed',
        ]);
    }

    public function cancelledOrders()
    {
        return view('user.account.orders', [
            'orders' => $this->filterUserOrders(fn($o) => ($o['status'] ?? '') === 'cancelled'),
            'activeTab' => 'cancelled',
        ]);
    }

    public function returnedOrders()
    {
        return view('user.account.orders', [
            'orders' => $this->filterUserOrders(fn($o) => \in_array($o['status'] ?? '', ['returned', 'return_requested'], true)),
            'activeTab' => 'returned',
        ]);
    }

    public function orderShow(string $id)
    {
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;

        abort_unless($order, 404);
        abort_unless(($order['user_id'] ?? null) === Auth::id(), 403);

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

        $stageKey = OrderTimeline::currentKey($order);
        $rawStatus = $order['status'] ?? '';
        // Khi user đã xác nhận nhận hàng (status=received) — coi như hoàn tất, không còn confirm/return
        $isReceived = $rawStatus === 'received';
        $isReturned = \in_array($rawStatus, ['returned', 'return_requested'], true);

        $canCancel          = \in_array($stageKey, ['placed', 'pending', 'confirmed'], true)
                              && !\in_array($rawStatus, ['cancelled', 'returned', 'return_requested', 'received'], true);
        $canConfirmReceived = $stageKey === 'delivered' && !$isReceived && !$isReturned && $rawStatus !== 'cancelled';
        $canReturn          = $stageKey === 'delivered' && !$isReceived && !$isReturned && $rawStatus !== 'cancelled';

        return view('user.account.order-detail', [
            'order'               => $order,
            'timeline'            => $timeline,
            'itemReviews'         => $itemReviews,
            'canReview'           => !$timeline['cancelled'] && $stageKey === 'delivered' && !$isReturned,
            'canCancel'           => $canCancel,
            'canConfirmReceived'  => $canConfirmReceived,
            'canReturn'           => $canReturn,
        ]);
    }

    public function confirmReceived(string $id)
    {
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;

        abort_unless($order, 404);
        abort_unless(($order['user_id'] ?? null) === Auth::id(), 403);

        $stageKey = OrderTimeline::currentKey($order);
        if ($stageKey !== 'delivered') {
            return back()->with('cart_flash', 'Chỉ có thể xác nhận khi đơn đã giao thành công.');
        }
        if (\in_array($order['status'] ?? '', ['received', 'returned', 'return_requested', 'cancelled'], true)) {
            return back()->with('cart_flash', 'Đơn này không thể xác nhận nhận hàng.');
        }

        $orders[$id]['status']       = 'received';
        $orders[$id]['received_at']  = now()->toDateTimeString();
        session(['orders' => $orders]);

        Notifications::push([
            'id'      => "ORDER-{$id}-received",
            'type'    => 'order',
            'title'   => "Đơn #{$id} đã hoàn tất",
            'content' => 'Cảm ơn bạn đã mua sắm tại CozyYarn! Đừng quên để lại đánh giá cho sản phẩm nhé.',
            'link'    => "/don-hang/{$id}",
            'icon'    => 'order-received',
            'meta'    => ['order_id' => $id],
        ]);

        return back()->with('cart_flash', 'Cảm ơn bạn! Đã xác nhận nhận hàng thành công.');
    }

    public function cancelOrder(Request $request, string $id)
    {
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;

        abort_unless($order, 404);
        abort_unless(($order['user_id'] ?? null) === Auth::id(), 403);

        $stageKey = OrderTimeline::currentKey($order);
        if (!\in_array($stageKey, ['placed', 'pending', 'confirmed'], true)) {
            return back()->with('cart_flash', 'Đơn đã chuyển sang vận chuyển, không thể huỷ.');
        }

        $reason = trim((string) $request->input('reason', ''));
        $orders[$id]['status']         = 'cancelled';
        $orders[$id]['cancelled_at']   = now()->toDateTimeString();
        $orders[$id]['cancel_reason']  = $reason !== '' ? $reason : 'Không có lý do cụ thể';
        session(['orders' => $orders]);

        Notifications::push([
            'id'      => "ORDER-{$id}-cancelled",
            'type'    => 'order',
            'title'   => "Đơn #{$id} đã được huỷ",
            'content' => 'Bạn đã huỷ đơn thành công. Nếu đã thanh toán online, tiền sẽ được hoàn trong 3–5 ngày làm việc.',
            'link'    => "/don-hang/{$id}",
            'icon'    => 'order-cancelled',
            'meta'    => ['order_id' => $id],
        ]);

        return redirect()->route('user.orders.cancelled')->with('cart_flash', 'Đã huỷ đơn hàng.');
    }

    public function requestReturn(Request $request, string $id)
    {
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;

        abort_unless($order, 404);
        abort_unless(($order['user_id'] ?? null) === Auth::id(), 403);

        $stageKey = OrderTimeline::currentKey($order);
        if ($stageKey !== 'delivered') {
            return back()->with('cart_flash', 'Chỉ có thể yêu cầu trả hàng sau khi đơn đã giao thành công.');
        }
        if (\in_array($order['status'] ?? '', ['returned', 'return_requested'], true)) {
            return back()->with('cart_flash', 'Đơn này đã được gửi yêu cầu trả hàng.');
        }

        $reason = trim((string) $request->input('reason', ''));
        $orders[$id]['status']         = 'return_requested';
        $orders[$id]['returned_at']    = now()->toDateTimeString();
        $orders[$id]['return_reason']  = $reason !== '' ? $reason : 'Không có lý do cụ thể';
        session(['orders' => $orders]);

        Notifications::push([
            'id'      => "ORDER-{$id}-return",
            'type'    => 'order',
            'title'   => "Đã nhận yêu cầu trả hàng cho đơn #{$id}",
            'content' => 'Shop đang xử lý yêu cầu trả hàng & hoàn tiền. Bạn sẽ nhận được phản hồi trong 24 giờ.',
            'link'    => "/don-hang/{$id}",
            'icon'    => 'order-returned',
            'meta'    => ['order_id' => $id],
        ]);

        return redirect()->route('user.orders.returned')->with('cart_flash', 'Đã gửi yêu cầu trả hàng.');
    }

    /**
     * Lọc đơn của user hiện tại theo callback, sắp xếp mới → cũ.
     */
    private function filterUserOrders(callable $filter): array
    {
        $userId = Auth::id();
        $all    = session('orders', []);
        $mine   = array_filter($all, fn($o) => ($o['user_id'] ?? null) === $userId && $filter($o));
        uasort($mine, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $mine;
    }
}
