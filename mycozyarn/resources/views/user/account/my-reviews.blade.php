@extends('layouts.public')

@section('title', 'Đánh giá của tôi — CozyYarn')

@section('content')
<section class="acc-page">
    <div class="acc-page__inner">
        <div class="acc-page__head">
            <span class="section-chip">Tài khoản</span>
            <h1 class="acc-page__title">Đánh giá của tôi</h1>
            <p class="acc-page__sub">
                @if(count($reviews) > 0)
                    Bạn đã đánh giá {{ count($reviews) }} sản phẩm.
                @else
                    Bạn chưa viết đánh giá nào — đánh giá sản phẩm sau khi đơn được giao nhé.
                @endif
            </p>
        </div>

        <div class="acc-layout">
            @include('user.account._sidebar', ['active' => 'reviews'])

            <div class="acc-content">
                @if(count($reviews) === 0)
                    <div class="cart-empty">
                        <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                            <circle cx="60" cy="60" r="52" fill="#fde4ee"/>
                            <path d="M60 30l9 20 21 3-16 15 4 22-18-10-18 10 4-22-16-15 21-3z" fill="#d97b9d"/>
                        </svg>
                        <h3>Chưa có đánh giá nào</h3>
                        <p>Sau khi đơn hàng được giao, bạn có thể viết đánh giá cho sản phẩm.</p>
                        <a href="{{ route('user.orders') }}" class="cart-btn cart-btn--primary">Xem đơn hàng</a>
                    </div>
                @else
                    <div class="rv-list">
                        @foreach($reviews as $r)
                            <article class="rv-mine">
                                <a href="{{ route('shop.product', ['category' => $r['product_category'], 'product' => $r['product_slug']]) }}" class="rv-mine__img">
                                    <img src="{{ $r['product_image'] ?? '/images/1.jpg' }}" alt="{{ $r['product_name'] }}">
                                </a>
                                <div class="rv-mine__body">
                                    <div class="rv-mine__head">
                                        <a href="{{ route('shop.product', ['category' => $r['product_category'], 'product' => $r['product_slug']]) }}" class="rv-mine__name">
                                            {{ $r['product_name'] }}
                                        </a>
                                        @if(!empty($r['variant']) || !empty($r['size']))
                                            <span class="rv-mine__meta">
                                                @if(!empty($r['variant'])){{ $r['variant'] }}@endif
                                                @if(!empty($r['variant']) && !empty($r['size'])) · @endif
                                                @if(!empty($r['size'])){{ $r['size'] }}@endif
                                            </span>
                                        @endif
                                    </div>

                                    <div class="star-row">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span class="star @if($s <= $r['rating']) is-on @endif">★</span>
                                        @endfor
                                        <span class="rv-mine__date">{{ \Carbon\Carbon::parse($r['created_at'])->format('H:i d/m/Y') }}</span>
                                    </div>

                                    @if(!empty($r['comment']))
                                        <p class="rv-mine__comment">{{ $r['comment'] }}</p>
                                    @else
                                        <p class="rv-mine__comment rv-mine__comment--empty">(Không có bình luận)</p>
                                    @endif

                                    <div class="rv-mine__foot">
                                        <a href="{{ route('user.orders.show', ['id' => $r['order_id']]) }}" class="rv-mine__link">
                                            Xem đơn #{{ $r['order_id'] }}
                                        </a>
                                        <form method="POST" action="{{ route('user.reviews.destroy') }}" onsubmit="return confirm('Xoá đánh giá này?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id" value="{{ $r['id'] }}">
                                            <button type="submit" class="od-review__del">Xoá</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
