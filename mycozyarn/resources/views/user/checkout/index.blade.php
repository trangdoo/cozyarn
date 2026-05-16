@extends('layouts.public')

@section('title', 'Thanh toán — CozyYarn')

@section('content')
<section class="checkout-page">
    <div class="checkout-page__inner">
        <div class="checkout-page__head">
            <span class="section-chip">Thanh toán</span>
            <h1 class="checkout-page__title">Thông tin đặt hàng</h1>
            <div class="checkout-steps">
                <span class="checkout-step is-done">1. Giỏ hàng</span>
                <span class="checkout-step is-active">2. Thông tin &amp; thanh toán</span>
                <span class="checkout-step">3. Hoàn tất</span>
            </div>
        </div>

        @if($errors->any() && !$errors->has('keys') && !$errors->has('checkout'))
            <div class="cart-alert">
                <ul style="margin:0;padding-left:18px">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.place') }}" class="checkout-layout">
            @csrf

            {{-- LEFT: Address + payment --}}
            <div class="checkout-main">
                <section class="co-card">
                    <h3 class="co-card__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.9-3.1-7-7-7Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        Địa chỉ giao hàng
                    </h3>

                    <div class="co-grid-2">
                        <label class="co-field">
                            <span class="co-label">Họ và tên <em>*</em></span>
                            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Nguyễn Văn A" required>
                        </label>
                        <label class="co-field">
                            <span class="co-label">Số điện thoại <em>*</em></span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="0912 345 678" required>
                        </label>
                    </div>

                    <div class="co-grid-2">
                        <label class="co-field">
                            <span class="co-label">Tỉnh / Thành phố <em>*</em></span>
                            <input type="text" name="province" value="{{ old('province') }}" placeholder="Hà Nội" required list="co-province-list">
                            <datalist id="co-province-list">
                                <option value="Hà Nội"></option>
                                <option value="TP. Hồ Chí Minh"></option>
                                <option value="Đà Nẵng"></option>
                                <option value="Hải Phòng"></option>
                                <option value="Cần Thơ"></option>
                                <option value="Huế"></option>
                                <option value="Nha Trang"></option>
                            </datalist>
                        </label>
                        <label class="co-field">
                            <span class="co-label">Quận / Huyện <em>*</em></span>
                            <input type="text" name="district" value="{{ old('district') }}" placeholder="Nam Từ Liêm" required>
                        </label>
                    </div>

                    <label class="co-field">
                        <span class="co-label">Địa chỉ cụ thể <em>*</em></span>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Số nhà, tên đường, phường..." required>
                    </label>

                    <label class="co-field">
                        <span class="co-label">Ghi chú</span>
                        <textarea name="note" rows="3" placeholder="Ghi chú cho shop (tuỳ chọn)">{{ old('note') }}</textarea>
                    </label>
                </section>

                <section class="co-card">
                    <h3 class="co-card__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                        Phương thức thanh toán
                    </h3>
                    <div class="co-pay-list">
                        <label class="co-pay">
                            <input type="radio" name="payment" value="cod" @checked(old('payment','cod')==='cod') required>
                            <span class="co-pay__dot"></span>
                            <span class="co-pay__icon" style="background:#fde4ee;color:#b55a82">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="10" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                            <span class="co-pay__body">
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <small>Kiểm tra hàng trước khi thanh toán — phổ biến, an toàn.</small>
                            </span>
                        </label>
                        <label class="co-pay">
                            <input type="radio" name="payment" value="bank" @checked(old('payment')==='bank')>
                            <span class="co-pay__dot"></span>
                            <span class="co-pay__icon" style="background:#e0f0ff;color:#3b7fc2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10l9-5 9 5v2H3zM5 12v6M9 12v6M15 12v6M19 12v6M3 20h18"/></svg>
                            </span>
                            <span class="co-pay__body">
                                <strong>Chuyển khoản ngân hàng</strong>
                                <small>Sau khi đặt hàng sẽ hiện QR VietQR · Hệ thống tự xác nhận khi tiền về.</small>
                            </span>
                        </label>
                        <label class="co-pay">
                            <input type="radio" name="payment" value="momo" @checked(old('payment')==='momo')>
                            <span class="co-pay__dot"></span>
                            <span class="co-pay__icon" style="background:#fde0ee;color:#a50064">
                                <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="7" cy="8" r="3"/><circle cx="17" cy="8" r="3"/><circle cx="7" cy="16" r="3"/><circle cx="17" cy="16" r="3"/></svg>
                            </span>
                            <span class="co-pay__body">
                                <strong>Ví MoMo</strong>
                                <small>Quét mã QR MoMo sau khi xác nhận đơn hàng.</small>
                            </span>
                        </label>
                    </div>

                    <label class="co-field" style="margin-top:14px">
                        <span class="co-label">Mã giảm giá (nếu có)</span>
                        <input type="text" name="discount_code" value="{{ old('discount_code') }}" placeholder="VD: COZY10" maxlength="40" style="text-transform:uppercase">
                    </label>

                    {!! \App\Plugin\Hook::render('checkout.payment_extra') !!}
                </section>
            </div>

            {{-- RIGHT: Order summary --}}
            <aside class="co-summary">
                <h3 class="co-card__title" style="margin-top:0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/></svg>
                    Đơn hàng ({{ count($items) }} sản phẩm)
                </h3>

                <div class="co-item-list">
                    @foreach($items as $item)
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
                    <strong>{{ number_format($subtotal, 0, ',', '.') }} ₫</strong>
                </div>
                <div class="co-summary__row">
                    <span>Phí vận chuyển</span>
                    @if($shippingFee === 0)
                        <strong style="color:#3d7a52">Miễn phí</strong>
                    @else
                        <strong>{{ number_format($shippingFee, 0, ',', '.') }} ₫</strong>
                    @endif
                </div>
                @if($shippingFee > 0)
                    <p class="co-summary__hint">Miễn phí vận chuyển cho đơn từ 500.000 ₫ hoặc mua từ 10 sản phẩm.</p>
                @endif
                <div class="co-summary__total">
                    <span>Tổng thanh toán</span>
                    <strong>{{ number_format($total, 0, ',', '.') }} ₫</strong>
                </div>

                <button type="submit" class="cart-btn cart-btn--primary cart-btn--block">
                    Đặt hàng
                </button>

                <a href="{{ route('cart.index') }}" class="co-back">← Quay lại giỏ hàng</a>
            </aside>
        </form>
    </div>
</section>
@endsection
