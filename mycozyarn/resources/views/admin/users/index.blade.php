@extends('layouts.app')

@section('content')

<!-- HERO -->
<section class="hero">
    <div class="hero-left">
        <h1>Len xinh cho mùa đông 🧶</h1>
        <p>Mềm mại - Ấm áp - Phong cách</p>

        <div class="hero-btns">
            <button class="btn-main">Mua ngay</button>
            <button class="btn-outline">Xem sản phẩm</button>
        </div>
    </div>

    <div class="hero-right">
        <img src="https://i.imgur.com/8Km9tLL.png" alt="yarn">
    </div>
</section>

<!-- PRODUCT -->
<section class="products">
    <h2>Sản phẩm nổi bật</h2>

    <div class="product-grid">
        @for ($i = 0; $i < 6; $i++)
            @include('components.user.product-card')
        @endfor
    </div>
</section>

@endsection