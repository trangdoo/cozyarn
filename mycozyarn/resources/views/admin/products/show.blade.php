@extends('layouts.admin')

@section('title', $product->name . ' — Chi tiết')
@section('page_title', 'Chi tiết sản phẩm')

@php
    $active = 'products';
    $catSlug = $product->category?->slug ?? '';
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.products.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $product->name }}</h1>
            <p><code class="admin-code">{{ $product->slug }}</code> · {{ $category?->name ?? '' }}</p>
        </div>
        <div class="admin-page__actions">
            @if($catSlug)
                <a href="{{ route('shop.product', ['category' => $catSlug, 'product' => $product->slug]) }}"
                   target="_blank" class="admin-btn admin-btn--ghost">↗ Xem trên shop</a>
                <form method="POST" action="{{ route('admin.products.duplicate', ['category' => $catSlug, 'product' => $product->slug]) }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn--ghost">⎘ Sao chép</button>
                </form>
                <a href="{{ route('admin.products.edit', ['category' => $catSlug, 'product' => $product->slug]) }}" class="admin-btn admin-btn--primary">✎ Sửa</a>
            @endif
        </div>
    </div>

    <div class="admin-grid-2">
        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin cơ bản</h2></header>
            <div class="admin-show__image">
                <img src="{{ $product->thumbnail ?? '/images/1.jpg' }}" alt="">
            </div>
            <ul class="admin-info">
                <li><span>ProductID</span><strong><code class="admin-code">{{ $product->slug }}</code></strong></li>
                <li><span>ProductName</span><strong>{{ $product->name }}</strong></li>
                <li><span>ProductType</span><strong>{{ $category?->name ?? $catSlug }}</strong></li>
                <li><span>Price</span><strong style="color:#b55a82">{{ number_format((float) $product->price, 0, ',', '.') }}₫</strong></li>
                @if(!empty($product->old_price))
                    <li><span>Giá gốc</span><strong style="text-decoration:line-through;color:#b09aa4">{{ number_format((float) $product->old_price, 0, ',', '.') }}₫</strong></li>
                @endif
                <li><span>Quantity</span><strong>{{ $product->stock_quantity }}</strong></li>
                <li><span>Unit</span><strong>{{ $product->unit }}</strong></li>
                <li><span>Status</span><strong><span class="admin-badge admin-badge--{{ $product->status }}">{{ $product->status === 'active' ? 'Đang bán' : 'Ngưng' }}</span></strong></li>
                @if(!empty($product->tag))
                    <li><span>Tag</span><strong>{{ $product->tag }}</strong></li>
                @endif
                <li><span>CreateDate</span><strong>{{ optional($product->created_at)->format('H:i · d/m/Y') }}</strong></li>
                <li><span>UpdateDate</span><strong>{{ optional($product->updated_at)->format('H:i · d/m/Y') }}</strong></li>
            </ul>
        </section>

        <section class="admin-card">
            <header class="admin-card__head"><h2>Mô tả</h2></header>
            <div class="admin-show__desc">
                <p><strong>{{ $product->short_desc ?? '' }}</strong></p>
                @if(!empty($product->description))
                    <p>{{ $product->description }}</p>
                @endif
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('keydown', (e) => {
    if (e.altKey && (e.key === 'e' || e.key === 'E') && @json($catSlug !== '')) {
        e.preventDefault();
        window.location = @json($catSlug ? route('admin.products.edit', ['category' => $catSlug, 'product' => $product->slug]) : '');
    } else if (e.key === 'Escape') {
        window.location = @json(route('admin.products.index'));
    }
});
</script>
@endsection
