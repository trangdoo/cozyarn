<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\BuyNowRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\StartCheckoutRequest;
use App\Support\Cart;

class CheckoutController
{
    private const int FREE_SHIP_THRESHOLD = 500000;
    private const int FREE_SHIP_QTY = 10;
    private const int SHIPPING_FEE = 25000;

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

        $orderId = 'CZ' . strtoupper(bin2hex(random_bytes(4)));

        $order = [
            'id'          => $orderId,
            'items'       => array_values($selected),
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'total'       => $total,
            'name'        => $data['name'],
            'phone'       => $data['phone'],
            'province'    => $data['province'],
            'district'    => $data['district'],
            'address'     => $data['address'],
            'note'        => $data['note'] ?? '',
            'payment'     => $data['payment'],
            'status'      => 'pending',
            'created_at'  => now()->toDateTimeString(),
            'user_id'     => auth()->id(),
        ];

        $orders = session('orders', []);
        $orders[$orderId] = $order;
        session(['orders' => $orders]);

        foreach ($keys as $k) {
            Cart::remove($k);
        }
        session()->forget('checkout_keys');

        return redirect()->route('checkout.success', ['id' => $orderId]);
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
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;
        abort_unless($order, 404);
        return view('user.checkout.success', ['order' => $order]);
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
}
