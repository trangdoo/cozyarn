@extends('layouts.admin')

@section('title', $product['name'] . ' — Chi tiết')
@section('page_title', 'Chi tiết sản phẩm')

@php $active = 'products'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.products.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $product['name'] }}</h1>
            <p><code class="admin-code">{{ $product['slug'] }}</code> · {{ $category['name'] ?? '' }}</p>
        </div>
        <div class="admin-page__actions">
            <a href="{{ route('shop.product', ['category' => $product['category_slug'], 'product' => $product['slug']]) }}"
               target="_blank" class="admin-btn admin-btn--ghost">↗ Xem trên shop</a>
            <form method="POST" action="{{ route('admin.products.duplicate', ['category' => $product['category_slug'], 'slug' => $product['slug']]) }}">
                @csrf
                <button type="submit" class="admin-btn admin-btn--ghost">⎘ Sao chép</button>
            </form>
            <a href="{{ route('admin.products.edit', ['category' => $product['category_slug'], 'slug' => $product['slug']]) }}" class="admin-btn admin-btn--primary">✎ Sửa</a>
        </div>
    </div>

    <div class="admin-grid-2">
        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin cơ bản</h2></header>
            <div class="admin-show__image">
                <img src="{{ $product['image'] ?? '/images/1.jpg' }}" alt="">
            </div>
            <ul class="admin-info">
                <li><span>ProductID</span><strong><code class="admin-code">{{ $product['slug'] }}</code></strong></li>
                <li><span>ProductName</span><strong>{{ $product['name'] }}</strong></li>
                <li><span>ProductType</span><strong>{{ $category['name'] ?? $product['category_slug'] }}</strong></li>
                <li><span>Price</span><strong style="color:#b55a82">{{ number_format($product['price'], 0, ',', '.') }}₫</strong></li>
                @if(!empty($product['oldPrice']))
                    <li><span>Giá gốc</span><strong style="text-decoration:line-through;color:#b09aa4">{{ number_format($product['oldPrice'], 0, ',', '.') }}₫</strong></li>
                @endif
                <li><span>Quantity</span><strong>{{ $product['quantity'] }}</strong></li>
                <li><span>Unit</span><strong>{{ $product['unit'] }}</strong></li>
                <li><span>Status</span><strong><span class="admin-badge admin-badge--{{ $product['status'] ?? 'active' }}">{{ ($product['status'] ?? 'active') === 'active' ? 'Đang bán' : 'Ngưng' }}</span></strong></li>
                @if(!empty($product['tag']))
                    <li><span>Tag</span><strong>{{ $product['tag'] }}</strong></li>
                @endif
                <li><span>CreateDate</span><strong>{{ \Carbon\Carbon::parse($product['created_at'])->format('H:i · d/m/Y') }}</strong></li>
                <li><span>UpdateDate</span><strong>{{ \Carbon\Carbon::parse($product['updated_at'])->format('H:i · d/m/Y') }}</strong></li>
            </ul>
        </section>

        <section class="admin-card">
            <header class="admin-card__head"><h2>Mô tả</h2></header>
            <div class="admin-show__desc">
                <p><strong>{{ $product['shortDesc'] ?? '' }}</strong></p>
                @if(!empty($product['desc']))
                    <p>{{ $product['desc'] }}</p>
                @endif
            </div>
            @if(!empty($product['features']))
                <header class="admin-card__head"><h2>Đặc điểm nổi bật</h2></header>
                <ul class="admin-show__features">
                    @foreach($product['features'] as $f)
                        <li>✓ {{ $f }}</li>
                    @endforeach
                </ul>
            @endif
            @if(!empty($product['variants']))
                <header class="admin-card__head"><h2>Phân loại / Màu ({{ count($product['variants']) }})</h2></header>
                <div class="admin-show__variants">
                    @foreach($product['variants'] as $v)
                        <div class="admin-show__variant">
                            <span class="admin-show__swatch" style="background:{{ $v['color'] ?? '#eee' }}"></span>
                            <div>
                                <strong>{{ $v['label'] ?? '' }}</strong>
                                <small>Còn {{ $v['stock'] ?? 0 }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>

<script>
document.addEventListener('keydown', (e) => {
    if (e.altKey && (e.key === 'e' || e.key === 'E')) {
        e.preventDefault();
        window.location = @json(route('admin.products.edit', ['category' => $product['category_slug'], 'slug' => $product['slug']]));
    } else if (e.key === 'Escape') {
        window.location = @json(route('admin.products.index'));
    }
});
</script>
@endsection
