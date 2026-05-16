<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\BuyNowRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\StartCheckoutRequest;
use App\Support\AdminInbox;
use App\Support\Cart;
use App\Support\OrderStore;
use Illuminate\Http\JsonResponse;

class CheckoutController
{
    private const int FREE_SHIP_THRESHOLD = 500000;
    private const int FREE_SHIP_QTY = 10;
    private const int SHIPPING_FEE = 25000;
    // Tiền tố cho nội dung chuyển khoản — webhook SePay sẽ strip prefix này để match
    // order id (xem WebHookController::extractOrderId).
    private const string PAYMENT_MEMO_PREFIX = 'DH';

    /** Bước 1: nhận selected keys từ cart, lưu vào session rồi redirect sang GET. */
    public function start(StartCheckoutRequest $request)
    {
        $data  = $request->validated();
        $items = Cart::items();
        $keys  = array_values(array_filter($data['keys'], fn ($k) => isset($items[$k])));

        if (empty($keys)) {
            return back()->withErrors(['keys' => 'Không có sản phẩm hợp lệ nào được chọn.']);
        }

        session(['checkout_keys' => $keys]);
        return redirect()->route('checkout.index');
    }

    /** Bước 2: hiện form địa chỉ + order summary. */
    public function index()
    {
        $keys  = session('checkout_keys', []);
        $items = Cart::items();

        $selected = [];
        foreach ($keys as $k) {
            if (isset($items[$k])) $selected[$k] = $items[$k];
        }

        if (empty($selected)) {
            return redirect()->route('cart.index')
                ->withErrors(['checkout' => 'Chưa có sản phẩm nào được chọn để thanh toán.']);
        }

        [$subtotal, $shippingFee, $total] = $this->calcTotals($selected);

        return view('user.checkout.index', [
            'items'       => $selected,
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'total'       => $total,
            'user'        => auth()->user(),
        ]);
    }

    /** Bước 3: user submit form → tạo đơn hàng → redirect success. */
    public function place(CreateOrderRequest $request)
    {
        $data  = $request->validated();
        $keys  = session('checkout_keys', []);
        $items = Cart::items();

        $selected = [];
        foreach ($keys as $k) {
            if (isset($items[$k])) $selected[$k] = $items[$k];
        }

        if (empty($selected)) {
            return redirect()->route('cart.index');
        }

        [$subtotal, $shippingFee, $total] = $this->calcTotals($selected);

        // HOOK plugin: cho phép plugin (vd: DiscountCode) chỉnh lại tổng tiền checkout.
        $code     = trim((string) $request->input('discount_code', ''));
        $newTotal = (int) \App\Plugin\Hook::filter('checkout.total', $total, [
            'code'        => $code,
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'items'       => $selected,
        ]);
        $discount = max(0, $total - $newTotal);
        $total    = $newTotal;

        // Persist toàn bộ order + items + customer info vào DB qua OrderStore.
        // Single source of truth — admin/user các session khác đều thấy được.
        $orderModel = OrderStore::create(
            (int) auth()->id(),
            $data,
            $selected,
            [
                'subtotal'      => $subtotal,
                'shippingFee'   => $shippingFee,
                'discount'      => $discount,
                'discount_code' => $discount > 0 ? $code : null,
                'total'         => $total,
            ],
        );
        $orderId = (string) $orderModel->id;

        foreach ($keys as $k) {
            Cart::remove($k);
        }
        session()->forget('checkout_keys');

        // Push admin inbox: COD đẩy ngay "đơn mới chờ xác nhận"; bank đợi webhook xác
        // nhận thanh toán xong mới đẩy "đã thanh toán — chờ admin duyệt" (xem
        // WebHookController::maybeMarkOrderPaid).
        if ($data['payment'] === 'cod') {
            AdminInbox::push([
                'type'    => 'order_new',
                'title'   => "Đơn hàng mới #DH{$orderId} (COD)",
                'content' => sprintf(
                    '%s · %s · %s ₫',
                    $data['name'],
                    $data['phone'],
                    number_format($total, 0, ',', '.'),
                ),
                'link'    => route('admin.orders.show', ['id' => $orderId]),
                'meta'    => ['order_id' => $orderId, 'payment' => 'cod', 'total' => $total],
            ]);
        }

        // Bank → trang QR riêng để user quét + chờ webhook SePay xác nhận.
        // COD / khác → trang thành công luôn.
        if ($data['payment'] === 'bank') {
            return redirect()->route('checkout.pay', ['id' => $orderId]);
        }
        return redirect()->route('checkout.success', ['id' => $orderId]);
    }

    /** Trang QR riêng — chỉ cho bank. Tự chuyển sang success khi đã paid. */
    public function pay(string $id)
    {
        $order = OrderStore::findForUser($id, (int) auth()->id());
        abort_unless($order, 404);

        // COD/khác hoặc đã thanh toán → không cần ở trang QR nữa.
        if (($order['payment'] ?? '') !== 'bank' || ($order['payment_status'] ?? 'pending') === 'paid') {
            return redirect()->route('checkout.success', ['id' => $id]);
        }

        $bank = $this->buildBankPayload($order);
        if (!$bank) {
            // Bank chưa cấu hình → fallback về success (shop sẽ liên hệ).
            return redirect()->route('checkout.success', ['id' => $id]);
        }

        return view('user.checkout.pay', [
            'order' => $order,
            'bank'  => $bank,
        ]);
    }

    /** AJAX poll: trả về trạng thái thanh toán hiện tại để client tự redirect. */
    public function payStatus(string $id): JsonResponse
    {
        $order = OrderStore::findForUser($id, (int) auth()->id());
        if (!$order) {
            return response()->json(['ok' => false], 404);
        }
        $status = $order['payment_status'] ?? 'pending';
        return response()->json([
            'ok'             => true,
            'payment_status' => $status,
            'redirect'       => $status === 'paid' ? route('checkout.success', ['id' => $id]) : null,
        ]);
    }

    /** "Mua ngay" — thêm sản phẩm vào giỏ rồi đi thẳng checkout với mỗi item đó. */
    public function buyNow(BuyNowRequest $request)
    {
        $data = $request->validated();
        $qty  = (int) ($data['qty'] ?? 1);
        $key  = Cart::makeKey(
            $data['category'],
            $data['slug'],
            $data['variant'] ?? null,
            $data['size'] ?? null
        );

        Cart::add($key, [
            'category' => $data['category'],
            'slug'     => $data['slug'],
            'name'     => $data['name'],
            'image'    => $data['image'] ?? null,
            'price'    => (int) $data['price'],
            'variant'  => $data['variant'] ?? null,
            'size'     => $data['size'] ?? null,
        ], $qty);

        session(['checkout_keys' => [$key]]);
        return redirect()->route('checkout.index');
    }

    /** Bước 4: hiện thông báo đặt hàng thành công. */
    public function success(string $id)
    {
        $order = OrderStore::findForUser($id, (int) auth()->id());
        abort_unless($order, 404);

        // Bank chưa thanh toán → đẩy về trang QR thay vì hiển thị "đặt hàng thành công"
        // (vì giao dịch chưa hoàn tất). Sau khi webhook flip status=paid, user sẽ được
        // redirect tự động sang đây từ trang QR.
        if (($order['payment'] ?? '') === 'bank' && ($order['payment_status'] ?? 'pending') !== 'paid') {
            return redirect()->route('checkout.pay', ['id' => $id]);
        }

        return view('user.checkout.success', [
            'order' => $order,
        ]);
    }

    private function calcTotals(array $items): array
    {
        $subtotal = 0;
        $qtySum   = 0;
        foreach ($items as $it) {
            $qty       = (int) $it['qty'];
            $subtotal += ((int) $it['price']) * $qty;
            $qtySum   += $qty;
        }
        $freeShip    = $subtotal >= self::FREE_SHIP_THRESHOLD || $qtySum >= self::FREE_SHIP_QTY;
        $shippingFee = $freeShip ? 0 : self::SHIPPING_FEE;
        return [$subtotal, $shippingFee, $subtotal + $shippingFee];
    }

    /**
     * Trả về payload để view hiển thị hướng dẫn chuyển khoản + ảnh VietQR (SePay).
     * Cấu hình bank trong config/services.php (sepay.bank).
     */
    private function buildBankPayload(array $order): ?array
    {
        $cfg = config('services.sepay.bank', []);
        if (empty($cfg['account_number']) || empty($cfg['bank'])) {
            return null;
        }
        $amount = (int) ($order['total'] ?? 0);
        $memo   = self::PAYMENT_MEMO_PREFIX . (string) ($order['id'] ?? '');
        $qrUrl  = sprintf(
            'https://qr.sepay.vn/img?bank=%s&acc=%s&amount=%d&des=%s&template=compact',
            urlencode($cfg['bank']),
            urlencode($cfg['account_number']),
            $amount,
            urlencode($memo),
        );
        return [
            'bank'           => $cfg['bank'],
            'bank_name'      => $cfg['bank_name'] ?? $cfg['bank'],
            'account_number' => $cfg['account_number'],
            'account_name'   => $cfg['account_name'] ?? '',
            'amount'         => $amount,
            'memo'           => $memo,
            'qr_url'         => $qrUrl,
        ];
    }
}
