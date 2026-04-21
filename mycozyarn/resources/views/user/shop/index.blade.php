@extends('layouts.public')

@section('title', 'Shop · CozyYarn')

@section('content')
<section class="shop-hero">
    <div class="shop-hero__deco shop-hero__deco--1" aria-hidden="true"></div>
    <div class="shop-hero__deco shop-hero__deco--2" aria-hidden="true"></div>

    <div class="shop-hero__inner">
        <nav class="policy-breadcrumb" aria-label="breadcrumb">
            <a href="/">Trang chủ</a>
            <span aria-hidden="true">›</span>
            <span class="policy-breadcrumb__current">Shop</span>
        </nav>
        <span class="section-chip">Danh mục</span>
        <h1 class="shop-hero__title">Khám phá CozyYarn</h1>
        <p class="shop-hero__sub">Chọn danh mục bạn yêu thích — từ len sợi pastel đến bộ kim móc chuyên nghiệp — CozyYarn có tất cả cho dự án handmade của bạn.</p>
    </div>
</section>

<section class="shop-cats">
    <div class="shop-cats__inner">
        <div class="shop-cats__grid">
            @foreach($categories as $cat)
                @php $count = count($products[$cat['slug']] ?? []); @endphp
                <a href="/shop/{{ $cat['slug'] }}" class="cat-card" data-reveal>
                    <div class="cat-card__img">
                        <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}">
                        <span class="cat-card__count">{{ $count }} sản phẩm</span>
                    </div>
                    <div class="cat-card__body">
                        <span class="cat-card__tag">{{ $cat['tag'] }}</span>
                        <h3 class="cat-card__name">{{ $cat['name'] }}</h3>
                        <p class="cat-card__desc">{{ $cat['desc'] }}</p>
                        <span class="cat-card__more">
                            Xem sản phẩm
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
