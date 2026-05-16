@extends('layouts.public')

@section('title', 'Đặt hàng thành công — CozyYarn')

@section('content')
<section class="checkout-page checkout-page--success">
    <div class="checkout-page__inner">
        <div class="success-hero">
            <div class="success-hero__icon" aria-hidden="true">
                <svg viewBox="0 0 80 80" fill="none">
                    <circle cx="40" cy="40" r="38" fill="#c3e8d5"/>
                    <circle cx="40" cy="40" r="30" fill="#3d7a52"/>
                    <path d="M24 40l12 12 20-24" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
            </div>
            <h1 class="success-hero__title">Đặt hàng thành công!</h1>
            <p class="success-hero__sub">
                @if(($order['payment'] ?? '') === 'bank')
                    Đã nhận được thanh toán. Cảm ơn bạn đã mua hàng tại CozyYarn 💕<br>
                    Shop sẽ liên hệ xác nhận đơn trong vòng 24 giờ.
                @else
                    Cảm ơn bạn đã mua hàng tại CozyYarn 💕<br>
                    Shop sẽ liên hệ xác nhận đơn trong vòng 24 giờ.
                @endif
            </p>
            <div class="success-hero__id">
                Mã đơn hàng: <strong>{{ $order['id'] }}</strong>
            </div>

            <div class="checkout-steps">
                <span class="checkout-step is-done">1. Giỏ hàng</span>
                <span class="checkout-step is-done">2. Thông tin &amp; thanh toán</span>
                <span class="checkout-step is-done">3. Hoàn tất</span>
            </div>
        </div>

        <div class="success-grid">
            <section class="co-card">
                <h3 class="co-card__title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.9-3.1-7-7-7Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    Thông tin giao hàng
                </h3>
                <ul class="success-info">
                    <li><span>Người nhận</span><strong>{{ $order['name'] }}</strong></li>
                    <li><span>Số điện thoại</span><strong>{{ $order['phone'] }}</strong></li>
                    <li><span>Địa chỉ</span><strong>{{ $order['address'] }}, {{ $order['district'] }}, {{ $order['province'] }}</strong></li>
                    @if(!empty($order['note']))
                        <li><span>Ghi chú</span><strong>{{ $order['note'] }}</strong></li>
                    @endif
                    <li>
                        <span>Phương thức thanh toán</span>
                        <strong>
                            @switch($order['payment'])
                                @case('cod') Thanh toán khi nhận hàng (COD) @break
                                @case('bank') Chuyển khoản ngân hàng @break
                                @case('momo') Ví MoMo @break
                                @default {{ $order['payment'] }}
                            @endswitch
                        </strong>
                    </li>
                    <li>
                        <span>Trạng thái</span>
                        <strong><span class="status-pill">Chờ xác nhận</span></strong>
                    </li>
                    @if(($order['payment'] ?? '') !== 'cod')
                        <li>
                            <span>Thanh toán</span>
                            <strong>
                                @if(($order['payment_status'] ?? 'pending') === 'paid')
                                    <span class="status-pill" style="background:#dcfce7;color:#166534">Đã thanh toán</span>
                                @else
                                    <span class="status-pill" style="background:#fef3c7;color:#92400e">Chờ thanh toán</span>
                                @endif
                            </strong>
                        </li>
                    @endif
                </ul>

            </section>

            <section class="co-card">
                <h3 class="co-card__title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/></svg>
                    Đơn hàng ({{ count($order['items']) }} sản phẩm)
                </h3>

                <div class="co-item-list">
                    @foreach($order['items'] as $item)
                        <div class="co-item">
                            <div class="co-item__img">
                                <img src="{{ $item['image'] ?? '/images/1.jpg' }}" alt="{{ $item['name'] }}">
                                <span class="co-item__qty">{{ $item['qty'] }}</span>
                            </div>
                            <div class="co-item__body">
                                <span class="co-item__name">{{ $item['name'] }}</span>
                                @if(!empty($item['variant']) || !empty($item['size']))
                                    <span class="co-item__meta">
                                        @if(!empty($item['variant'])){{ $item['variant'] }}@endif
                                        @if(!empty($item['variant']) && !empty($item['size'])) · @endif
                                        @if(!empty($item['size'])){{ $item['size'] }}@endif
                                    </span>
                                @endif
                            </div>
                            <div class="co-item__price">{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }} ₫</div>
                        </div>
                    @endforeach
                </div>

                <div class="co-summary__row">
                    <span>Tạm tính</span>
                    <strong>{{ number_format($order['subtotal'], 0, ',', '.') }} ₫</strong>
                </div>
                <div class="co-summary__row">
                    <span>Phí vận chuyển</span>
                    @if($order['shippingFee'] === 0)
                        <strong style="color:#3d7a52">Miễn phí</strong>
                    @else
                        <strong>{{ number_format($order['shippingFee'], 0, ',', '.') }} ₫</strong>
                    @endif
                </div>
                @if(!empty($order['discount']) && $order['discount'] > 0)
                    <div class="co-summary__row">
                        <span>Giảm giá
                            @if(!empty($order['discount_code']))
                                <code style="background:#fde4ee;padding:1px 6px;border-radius:6px;font-size:11px">{{ $order['discount_code'] }}</code>
                            @endif
                        </span>
                        <strong style="color:#b91c1c">−{{ number_format($order['discount'], 0, ',', '.') }} ₫</strong>
                    </div>
                @endif
                <div class="co-summary__total">
                    <span>Tổng cộng</span>
                    <strong>{{ number_format($order['total'], 0, ',', '.') }} ₫</strong>
                </div>
            </section>
        </div>

        <div class="success-actions">
            <a href="/shop" class="cart-btn cart-btn--primary">Tiếp tục mua sắm</a>
            <a href="/" class="cart-btn cart-btn--ghost">Về trang chủ</a>
        </div>
    </div>
</section>
@endsection
