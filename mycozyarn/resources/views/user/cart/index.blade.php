@extends('layouts.public')

@section('title', 'Giỏ hàng — CozyYarn')

@section('content')
<section class="cart-page">
    <div class="cart-page__inner">
        <div class="cart-page__head">
            <span class="section-chip">Giỏ hàng</span>
            <h1 class="cart-page__title">Giỏ hàng của bạn</h1>
            <p class="cart-page__sub">
                @if(count($items) > 0)
                    Bạn đang có {{ \App\Support\Cart::count() }} sản phẩm trong giỏ. Tích chọn sản phẩm muốn mua rồi bấm <strong>Thanh toán</strong>.
                @else
                    Giỏ của bạn đang trống — khám phá <a href="/shop">shop</a> để tìm sản phẩm yêu thích nhé.
                @endif
            </p>
            @if($errors->has('keys') || $errors->has('checkout'))
                <div class="cart-alert">{{ $errors->first('keys') ?: $errors->first('checkout') }}</div>
            @endif
        </div>

        @if(count($items) === 0)
            <div class="cart-empty">
                <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                    <circle cx="60" cy="60" r="52" fill="#fde4ee"/>
                    <path d="M38 44h8l4 30h32l4-22H50" stroke="#d97b9d" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <circle cx="56" cy="84" r="4" fill="#d97b9d"/>
                    <circle cx="78" cy="84" r="4" fill="#d97b9d"/>
                    <path d="M70 52l8 8M78 52l-8 8" stroke="#d97b9d" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                <h3>Giỏ hàng trống</h3>
                <p>Tiếp tục mua sắm và tìm cuộn len yêu thích của bạn nhé!</p>
                <a href="/shop" class="cart-btn cart-btn--primary">Khám phá shop</a>
            </div>
        @else
            {{-- Form thanh toán outer (checkboxes ở mỗi row sẽ liên kết qua form="co-form") --}}
            <form id="co-form" method="POST" action="{{ route('checkout.start') }}">
                @csrf
            </form>

            <div class="cart-layout">
                <div class="cart-list" data-cart-list>
                    <div class="cart-list__head">
                        <label class="cart-check-all">
                            <input type="checkbox" data-select-all checked>
                            <span>Chọn tất cả ({{ count($items) }})</span>
                        </label>
                        <span class="cart-list__head-col">Đơn giá</span>
                        <span class="cart-list__head-col">Số lượng</span>
                        <span class="cart-list__head-col">Thành tiền</span>
                        <span class="cart-list__head-col"></span>
                    </div>

                    @foreach($items as $key => $item)
                        <article class="cart-row" data-cart-row data-price="{{ $item['price'] }}" data-qty="{{ $item['qty'] }}">
                            <label class="cart-row__check">
                                <input type="checkbox" name="keys[]" value="{{ $key }}" form="co-form" data-item-check checked>
                            </label>

                            <a href="{{ route('shop.product', ['category' => $item['category'], 'product' => $item['slug']]) }}" class="cart-row__img">
                                <img src="{{ $item['image'] ?? '/images/1.jpg' }}" alt="{{ $item['name'] }}">
                            </a>
                            <div class="cart-row__body">
                                <a href="{{ route('shop.product', ['category' => $item['category'], 'product' => $item['slug']]) }}" class="cart-row__name">{{ $item['name'] }}</a>
                                @if(!empty($item['variant']) || !empty($item['size']))
                                    <div class="cart-row__meta">
                                        @if(!empty($item['variant']))<span>Màu: {{ $item['variant'] }}</span>@endif
                                        @if(!empty($item['size']))<span>Size: {{ $item['size'] }}</span>@endif
                                    </div>
                                @endif
                            </div>

                            <div class="cart-row__price">{{ number_format($item['price'], 0, ',', '.') }} ₫</div>

                            <form method="POST" action="{{ route('cart.update') }}" class="cart-row__qty-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="key" value="{{ $key }}">
                                <div class="cart-qty">
                                    <button type="submit" name="qty" value="{{ max(0, $item['qty'] - 1) }}" class="cart-qty__btn" aria-label="Giảm">−</button>
                                    <span class="cart-qty__val">{{ $item['qty'] }}</span>
                                    <button type="submit" name="qty" value="{{ $item['qty'] + 1 }}" class="cart-qty__btn" aria-label="Tăng">+</button>
                                </div>
                            </form>

                            <div class="cart-row__total">{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }} ₫</div>

                            <form method="POST" action="{{ route('cart.remove') }}" class="cart-row__remove-form">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="key" value="{{ $key }}">
                                <button type="submit" class="cart-row__remove" aria-label="Xoá">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13M9 7V4h6v3"/>
                                    </svg>
                                </button>
                            </form>
                        </article>
                    @endforeach

                    <div class="cart-list__actions">
                        <a href="/shop" class="cart-btn cart-btn--ghost">← Tiếp tục mua sắm</a>
                        <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Xoá toàn bộ giỏ hàng?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cart-btn cart-btn--ghost cart-btn--danger">Xoá tất cả</button>
                        </form>
                    </div>
                </div>

                <aside class="cart-summary">
                    <h3 class="cart-summary__title">Tóm tắt đơn hàng</h3>
                    <div class="cart-summary__row">
                        <span>Đã chọn</span>
                        <strong data-sum-count>{{ count($items) }} sản phẩm</strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>Tạm tính</span>
                        <strong data-sum-subtotal>{{ number_format($subtotal, 0, ',', '.') }} ₫</strong>
                    </div>
                    <div class="cart-summary__row">
                        <span>Phí vận chuyển</span>
                        <span class="cart-summary__muted">Tính khi thanh toán</span>
                    </div>
                    <div class="cart-summary__total">
                        <span>Tổng cộng</span>
                        <strong data-sum-total>{{ number_format($subtotal, 0, ',', '.') }} ₫</strong>
                    </div>
                    <button type="submit" form="co-form" class="cart-btn cart-btn--primary cart-btn--block" data-checkout-btn>
                        Thanh toán (<span data-sum-count-short>{{ count($items) }}</span>)
                    </button>
                    <p class="cart-summary__note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Thanh toán an toàn · Đổi trả 7 ngày
                    </p>
                </aside>
            </div>

            <script>
            (() => {
                const list    = document.querySelector('[data-cart-list]');
                if (!list) return;
                const rows    = list.querySelectorAll('[data-cart-row]');
                const all     = list.querySelector('[data-select-all]');
                const checks  = list.querySelectorAll('[data-item-check]');

                const fmt = (n) => n.toLocaleString('vi-VN') + ' ₫';
                const $ = (sel) => document.querySelector(sel);

                function recalc() {
                    let sub = 0, count = 0;
                    rows.forEach(row => {
                        const cb = row.querySelector('[data-item-check]');
                        if (!cb?.checked) return;
                        const price = parseInt(row.dataset.price, 10) || 0;
                        const qty   = parseInt(row.dataset.qty,   10) || 0;
                        sub  += price * qty;
                        count += qty;
                    });

                    $('[data-sum-subtotal]').textContent = fmt(sub);
                    $('[data-sum-total]').textContent    = fmt(sub);
                    $('[data-sum-count]').textContent    = count + ' sản phẩm';
                    $('[data-sum-count-short]').textContent = count;

                    const btn = $('[data-checkout-btn]');
                    btn.disabled = count === 0;
                    btn.classList.toggle('is-disabled', count === 0);

                    // Sync header checkbox state
                    const checkedCount = Array.from(checks).filter(c => c.checked).length;
                    all.checked = checkedCount === checks.length;
                    all.indeterminate = checkedCount > 0 && checkedCount < checks.length;
                }

                all?.addEventListener('change', () => {
                    checks.forEach(cb => cb.checked = all.checked);
                    recalc();
                });
                checks.forEach(cb => cb.addEventListener('change', recalc));

                recalc();
            })();
            </script>
        @endif
    </div>
</section>
@endsection
