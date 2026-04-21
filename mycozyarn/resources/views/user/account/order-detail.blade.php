@extends('layouts.public')

@section('title', 'Đơn ' . $order['id'] . ' — CozyYarn')

@php
    use App\Support\OrderTimeline;
    $statusMap = [
        'pending'   => ['Chờ xác nhận',     '#fff6cc', '#9a7a1f'],
        'placed'    => ['Đã đặt hàng',      '#fff6cc', '#9a7a1f'],
        'confirmed' => ['Chờ lấy hàng',     '#e0f0ff', '#2c5580'],
        'shipping'  => ['Chờ giao hàng',    '#fde4ee', '#b55a82'],
        'delivered' => ['Giao thành công',  '#c3e8d5', '#3d7a52'],
        'cancelled' => ['Đã huỷ',           '#ffe0e0', '#a63652'],
    ];
    $payMap = [
        'cod'  => 'Thanh toán khi nhận hàng (COD)',
        'bank' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví MoMo',
    ];
    $stageKey = OrderTimeline::currentKey($order);
    [$statusText, $statusBg, $statusColor] = $statusMap[$stageKey] ?? $statusMap['pending'];
    $payText = $payMap[$order['payment']] ?? $order['payment'];
@endphp

@section('content')
<section class="acc-page">
    <div class="acc-page__inner">
        <div class="acc-page__head">
            <span class="section-chip">Tài khoản</span>
            <h1 class="acc-page__title">Chi tiết đơn hàng</h1>
            <p class="acc-page__sub">
                <a href="{{ route('user.orders') }}">← Quay lại danh sách đơn</a>
            </p>
        </div>

        <div class="acc-layout">
            @include('user.account._sidebar', ['active' => 'orders'])

            <div class="acc-content">
                <div class="od-hero">
                    <div class="od-hero__left">
                        <span class="od-hero__label">Mã đơn hàng</span>
                        <strong class="od-hero__id">#{{ $order['id'] }}</strong>
                        <span class="od-hero__date">Đặt lúc {{ \Carbon\Carbon::parse($order['created_at'])->format('H:i · d/m/Y') }}</span>
                    </div>
                    <span class="order-card__status od-hero__status" style="background:{{ $statusBg }};color:{{ $statusColor }}">
                        {{ $statusText }}
                    </span>
                </div>

                {{-- TIMELINE --}}
                <section class="co-card">
                    <h3 class="co-card__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        Tiến độ đơn hàng
                    </h3>

                    @if($timeline['cancelled'])
                        <div class="cart-alert" style="text-align:center">
                            Đơn hàng đã bị huỷ.
                        </div>
                    @else
                        <ol class="timeline" style="--step-count: {{ count($timeline['steps']) }}">
                            @foreach($timeline['steps'] as $i => $step)
                                <li class="timeline__step @if($step['is_done']) is-done @endif @if($step['is_current']) is-current @endif">
                                    <span class="timeline__dot">
                                        @if($step['is_done'])
                                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 8l3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @elseif($step['is_current'])
                                            <span class="timeline__pulse"></span>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </span>
                                    <span class="timeline__label">{{ $step['label'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <p class="timeline__note">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                            Trạng thái sẽ tự động cập nhật theo thời gian.
                            @if(!$canReview && $stageKey !== 'delivered')
                                Bạn sẽ có thể đánh giá sản phẩm sau khi đơn hàng được giao thành công.
                            @endif
                        </p>
                    @endif
                </section>

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
                            <li><span>Thanh toán</span><strong>{{ $payText }}</strong></li>
                        </ul>
                    </section>

                    <section class="co-card">
                        <h3 class="co-card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/></svg>
                            Thanh toán
                        </h3>
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
                        <div class="co-summary__total">
                            <span>Tổng cộng</span>
                            <strong>{{ number_format($order['total'], 0, ',', '.') }} ₫</strong>
                        </div>
                    </section>
                </div>

                {{-- ITEMS + REVIEW FORMS --}}
                <section class="co-card">
                    <h3 class="co-card__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M3 12h18M3 18h12"/></svg>
                        Sản phẩm ({{ count($order['items']) }})
                    </h3>

                    <div class="od-items">
                        @foreach($order['items'] as $item)
                            @php
                                $itemKey = $item['key'] ?? '';
                                $existingReview = $itemReviews[$itemKey] ?? null;
                            @endphp
                            <article class="od-item">
                                <div class="od-item__main">
                                    <a href="{{ route('shop.product', ['category' => $item['category'], 'product' => $item['slug']]) }}" class="od-item__img">
                                        <img src="{{ $item['image'] ?? '/images/1.jpg' }}" alt="{{ $item['name'] }}">
                                        <span class="co-item__qty">{{ $item['qty'] }}</span>
                                    </a>
                                    <div class="od-item__body">
                                        <a href="{{ route('shop.product', ['category' => $item['category'], 'product' => $item['slug']]) }}" class="od-item__name">{{ $item['name'] }}</a>
                                        @if(!empty($item['variant']) || !empty($item['size']))
                                            <span class="od-item__meta">
                                                @if(!empty($item['variant'])){{ $item['variant'] }}@endif
                                                @if(!empty($item['variant']) && !empty($item['size'])) · @endif
                                                @if(!empty($item['size'])){{ $item['size'] }}@endif
                                            </span>
                                        @endif
                                    </div>
                                    <div class="od-item__price">{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }} ₫</div>
                                </div>

                                @if($existingReview)
                                    {{-- Đã đánh giá --}}
                                    <div class="od-review od-review--done">
                                        <div class="od-review__head">
                                            <span class="od-review__label">Đánh giá của bạn</span>
                                            <div class="star-row">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <span class="star @if($s <= $existingReview['rating']) is-on @endif">★</span>
                                                @endfor
                                            </div>
                                        </div>
                                        @if(!empty($existingReview['comment']))
                                            <p class="od-review__comment">{{ $existingReview['comment'] }}</p>
                                        @endif
                                        <div class="od-review__foot">
                                            <span class="od-review__date">{{ \Carbon\Carbon::parse($existingReview['created_at'])->format('H:i d/m/Y') }}</span>
                                            <form method="POST" action="{{ route('user.reviews.destroy') }}" onsubmit="return confirm('Xoá đánh giá này?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id" value="{{ $existingReview['id'] }}">
                                                <button type="submit" class="od-review__del">Xoá đánh giá</button>
                                            </form>
                                        </div>
                                    </div>
                                @elseif($canReview)
                                    {{-- Form đánh giá --}}
                                    <form method="POST" action="{{ route('user.reviews.store') }}" class="od-review od-review--form" data-review-form>
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                        <input type="hidden" name="item_key" value="{{ $itemKey }}">
                                        <input type="hidden" name="rating" value="5" data-rating-value>

                                        <div class="od-review__head">
                                            <span class="od-review__label">Đánh giá sản phẩm:</span>
                                            <div class="star-row star-row--input" data-star-input>
                                                @for($s = 1; $s <= 5; $s++)
                                                    <button type="button" class="star @if($s <= 5) is-on @endif" data-value="{{ $s }}" aria-label="{{ $s }} sao">★</button>
                                                @endfor
                                            </div>
                                        </div>
                                        <textarea name="comment" rows="2" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." maxlength="1000"></textarea>
                                        <div class="od-review__foot">
                                            <span class="od-review__hint">Tối đa 1000 ký tự</span>
                                            <button type="submit" class="cart-btn cart-btn--primary">Gửi đánh giá</button>
                                        </div>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>

<script>
(() => {
    // Star rating interaction: click chọn số sao
    document.querySelectorAll('[data-review-form]').forEach(form => {
        const starRow = form.querySelector('[data-star-input]');
        const hidden  = form.querySelector('[data-rating-value]');
        if (!starRow || !hidden) return;

        const stars = starRow.querySelectorAll('.star');
        const paint = (value) => {
            stars.forEach(s => {
                s.classList.toggle('is-on', parseInt(s.dataset.value, 10) <= value);
            });
        };

        stars.forEach(s => {
            s.addEventListener('click', () => {
                const v = parseInt(s.dataset.value, 10);
                hidden.value = v;
                paint(v);
            });
            s.addEventListener('mouseenter', () => paint(parseInt(s.dataset.value, 10)));
        });
        starRow.addEventListener('mouseleave', () => paint(parseInt(hidden.value, 10)));
    });
})();
</script>
@endsection
