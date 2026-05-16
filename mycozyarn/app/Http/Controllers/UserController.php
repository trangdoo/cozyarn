<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\Order;
use App\Services\UserService;
use App\Support\Notifications;
use App\Support\OrderStore;
use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function profile()
    {
        return view('user.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $this->users->updateProfile(Auth::user(), $request->validated());

        return back()->with('cart_flash', 'Cập nhật thông tin thành công.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        // Service tự throw ValidationException nếu pass cũ sai → redirect về form với lỗi.
        $this->users->changePassword(
            Auth::user(),
            $request->input('current_password'),
            $request->input('new_password'),
        );

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
        $order = OrderStore::findForUser($id, (int) Auth::id());
        abort_unless($order, 404);

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
            'canReview'           => !$timeline['cancelled'] && $isReceived && !$isReturned,
            'canCancel'           => $canCancel,
            'canConfirmReceived'  => $canConfirmReceived,
            'canReturn'           => $canReturn,
        ]);
    }

    public function confirmReceived(string $id)
    {
        $order = OrderStore::findForUser($id, (int) Auth::id());
        abort_unless($order, 404);

        $userName = Auth::user()->name ?? 'Khách';
        $ok = OrderStore::transition($id, 'received', ['delivered'], $userName, 'Khách xác nhận đã nhận hàng');
        if (!$ok) {
            return back()->with('cart_flash', 'Chỉ có thể xác nhận khi đơn đã giao thành công.');
        }

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
        $order = OrderStore::findForUser($id, (int) Auth::id());
        abort_unless($order, 404);

        $reason   = trim((string) $request->input('reason', ''));
        $userName = Auth::user()->name ?? 'Khách';
        $note     = 'Khách tự huỷ' . ($reason !== '' ? ': ' . $reason : '');
        $ok = OrderStore::transition($id, 'cancelled', ['placed', 'pending', 'confirmed'], $userName, $note);
        if (!$ok) {
            return back()->with('cart_flash', 'Đơn đã chuyển sang vận chuyển, không thể huỷ.');
        }

        Order::where('id', (int) $id)->update([
            'cancel_reason' => $reason !== '' ? $reason : 'Không có lý do cụ thể',
        ]);

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
        $order = OrderStore::findForUser($id, (int) Auth::id());
        abort_unless($order, 404);

        $stageKey = OrderTimeline::currentKey($order);
        if ($stageKey !== 'delivered') {
            return back()->with('cart_flash', 'Chỉ có thể yêu cầu trả hàng sau khi đơn đã giao thành công.');
        }
        if (\in_array($order['status'] ?? '', ['returned', 'return_requested'], true)) {
            return back()->with('cart_flash', 'Đơn này đã được gửi yêu cầu trả hàng.');
        }

        // Validate: bắt buộc 3 ảnh + 1 video làm bằng chứng
        $request->validate([
            'reason'   => 'nullable|string|max:300',
            'images'   => 'required|array|size:3',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'video'    => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska|max:51200',
        ], [
            'images.required'   => 'Vui lòng đính kèm đủ 3 ảnh bằng chứng.',
            'images.size'       => 'Phải có đúng 3 ảnh bằng chứng.',
            'images.*.image'    => 'File đính kèm phải là ảnh hợp lệ.',
            'images.*.mimes'    => 'Ảnh phải có định dạng jpg, png hoặc webp.',
            'images.*.max'      => 'Mỗi ảnh không vượt quá 5MB.',
            'video.required'    => 'Vui lòng đính kèm 1 video bằng chứng.',
            'video.mimetypes'   => 'Video phải có định dạng mp4, mov, webm hoặc mkv.',
            'video.max'         => 'Video không vượt quá 50MB.',
        ]);

        // Lưu files vào public/uploads/returns/{orderId}/
        $dest = public_path("uploads/returns/{$id}");
        if (!is_dir($dest)) @mkdir($dest, 0755, true);

        $imagePaths = [];
        foreach ($request->file('images') as $i => $file) {
            $ext  = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $name = "img-{$i}-" . Str::uuid()->toString() . ".{$ext}";
            $file->move($dest, $name);
            $imagePaths[] = "/uploads/returns/{$id}/{$name}";
        }

        $video     = $request->file('video');
        $videoExt  = strtolower($video->getClientOriginalExtension()) ?: 'mp4';
        $videoName = 'video-' . Str::uuid()->toString() . ".{$videoExt}";
        $video->move($dest, $videoName);
        $videoPath = "/uploads/returns/{$id}/{$videoName}";

        $reason   = trim((string) $request->input('reason', ''));
        $userName = Auth::user()->name ?? 'Khách';
        $note     = 'Khách yêu cầu trả hàng' . ($reason !== '' ? ': ' . $reason : '');
        OrderStore::transition($id, 'return_requested', ['delivered'], $userName, $note);

        Order::where('id', (int) $id)->update([
            'return_reason'   => $reason !== '' ? $reason : 'Không có lý do cụ thể',
            'return_evidence' => ['images' => $imagePaths, 'video' => $videoPath],
        ]);

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
     * Lọc đơn của user hiện tại theo callback (vẫn nhận shape array để giữ ngữ nghĩa cũ),
     * sắp xếp mới → cũ. Data lấy thẳng từ DB qua OrderStore.
     */
    private function filterUserOrders(callable $filter): array
    {
        $mine = array_filter(OrderStore::forUser((int) Auth::id()), $filter);
        // OrderStore::forUser đã sort desc theo created_at, không cần sort lại.
        return $mine;
    }
}
