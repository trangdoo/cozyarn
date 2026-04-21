@extends('layouts.public')

@section('title', ($q ? 'Tìm kiếm: ' . $q : 'Tìm kiếm') . ' — CozyYarn')

@section('content')
<section class="search-page">
    <div class="search-page__inner">
        <div class="search-page__head">
            <span class="section-chip">Tìm kiếm</span>
            @if($q)
                <h1 class="search-page__title">Kết quả cho "{{ $q }}"</h1>
                <p class="search-page__sub">
                    Tìm thấy <strong>{{ count($results) }}</strong> sản phẩm phù hợp.
                </p>
            @else
                <h1 class="search-page__title">Tìm kiếm sản phẩm</h1>
                <p class="search-page__sub">Nhập từ khoá bạn muốn tìm vào ô tìm kiếm ở đầu trang.</p>
            @endif
        </div>

        <form method="GET" action="{{ route('search') }}" class="search-page__form">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"/>
                <line x1="16.65" y1="16.65" x2="21" y2="21"/>
            </svg>
            <input type="search" name="q" value="{{ $q }}" placeholder="Tìm len, kim, kit..." autocomplete="off">
            <button type="submit">Tìm</button>
        </form>

        @if($q && count($results) === 0)
            <div class="search-empty">
                <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                    <circle cx="60" cy="60" r="52" fill="#fde4ee"/>
                    <circle cx="55" cy="55" r="22" stroke="#d97b9d" stroke-width="3" fill="none"/>
                    <line x1="72" y1="72" x2="90" y2="90" stroke="#d97b9d" stroke-width="3" stroke-linecap="round"/>
                    <path d="M48 55h14" stroke="#d97b9d" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                <h3>Không tìm thấy sản phẩm nào</h3>
                <p>Thử từ khoá khác hoặc xem <a href="/shop">toàn bộ shop</a> nhé.</p>
            </div>
        @endif

        @if(count($results) > 0)
            <div class="search-grid">
                @foreach($results as $p)
                    <article class="search-card">
                        <a class="search-card__img" href="{{ route('shop.product', ['category' => $p['category_slug'], 'product' => $p['slug']]) }}">
                            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}">
                            @if(!empty($p['tag']))
                                <span class="search-card__tag">{{ $p['tag'] }}</span>
                            @endif
                        </a>
                        <div class="search-card__body">
                            <span class="search-card__cat">{{ $p['category_name'] }}</span>
                            <h3 class="search-card__name">
                                <a href="{{ route('shop.product', ['category' => $p['category_slug'], 'product' => $p['slug']]) }}">{{ $p['name'] }}</a>
                            </h3>
                            <p class="search-card__desc">{{ $p['shortDesc'] ?? '' }}</p>
                            <div class="search-card__footer">
                                <span class="search-card__price">{{ number_format($p['price'], 0, ',', '.') }} ₫</span>
                                <a href="{{ route('shop.product', ['category' => $p['category_slug'], 'product' => $p['slug']]) }}" class="search-card__btn">Xem</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
