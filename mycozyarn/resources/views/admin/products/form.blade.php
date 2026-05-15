@extends('layouts.admin')

@php
    $isEdit = !empty($product);
    $catSlug = $isEdit ? ($product->category?->slug ?? '') : '';
    $action = $isEdit && $catSlug
        ? route('admin.products.update', ['category' => $catSlug, 'product' => $product->slug])
        : route('admin.products.store');
@endphp

@section('title', ($isEdit ? 'Sửa' : 'Thêm') . ' sản phẩm — CozyYarn')
@section('page_title', $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm')

@php $active = 'products'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.products.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="admin-card admin-form" data-product-form>
        @csrf
        @if($isEdit) @method('PATCH') @endif

        @if($errors->any())
            <div class="admin-errors">
                @foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="admin-form__row">
            <label style="flex:2">ProductName * <small>(Tên sản phẩm)</small>
                <input type="text" name="name" required autofocus value="{{ old('name', $product->name ?? '') }}">
            </label>
            <label>ProductType * <small>(Danh mục)</small>
                <select name="category_slug" required>
                    @foreach($categories as $slug => $c)
                        <option value="{{ $slug }}" @selected(old('category_slug', $catSlug) === $slug)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <label>Description * <small>(mô tả ngắn hiển thị ở card)</small>
            <input type="text" name="shortDesc" required maxlength="500" value="{{ old('shortDesc', $product->short_desc ?? '') }}">
        </label>

        <label>Mô tả chi tiết
            <textarea name="desc" rows="4" maxlength="3000">{{ old('desc', $product->description ?? '') }}</textarea>
        </label>

        <div class="admin-form__row">
            <label>Price (₫) *
                <input type="number" name="price" required min="0" value="{{ old('price', $product ? (int) $product->price : '') }}">
            </label>
            <label>Giá gốc (₫)
                <input type="number" name="oldPrice" min="0" value="{{ old('oldPrice', $product?->old_price ? (int) $product->old_price : '') }}">
            </label>
            <label>Quantity *
                <input type="number" name="quantity" required min="0" value="{{ old('quantity', $product->stock_quantity ?? 0) }}">
            </label>
            <label>Unit *
                <input type="text" name="unit" required maxlength="30" value="{{ old('unit', $product->unit ?? 'cuộn') }}" placeholder="cuộn, cái, bộ, gói...">
            </label>
        </div>

        <div class="admin-form__row">
            <label style="flex:2">PathImage <small>(URL ảnh)</small>
                <input type="text" name="image" value="{{ old('image', $product->thumbnail ?? '/images/1.jpg') }}">
            </label>
            <label>Tag
                <input type="text" name="tag" value="{{ old('tag', $product->tag ?? '') }}" placeholder="Hot, Mới, Sale...">
            </label>
            <label>Trạng thái *
                <select name="status" required>
                    <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Đang bán</option>
                    <option value="inactive" @selected(old('status', $product->status ?? '') === 'inactive')>Ngưng bán</option>
                </select>
            </label>
        </div>

        @if($isEdit)
            <div class="admin-form__meta">
                <span>ProductID: <code>{{ $product->slug }}</code></span>
                <span>CreateDate: {{ optional($product->created_at)->format('H:i · d/m/Y') }}</span>
                <span>UpdateDate: {{ optional($product->updated_at)->format('H:i · d/m/Y') }}</span>
            </div>
        @endif

        <div class="admin-form__actions">
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn--ghost" title="Huỷ (Esc)">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary" data-save-btn title="Lưu (Alt+S)">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo sản phẩm' }}</button>
        </div>
    </form>
</div>

<script>
(() => {
    document.addEventListener('keydown', (e) => {
        if (e.altKey && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            document.querySelector('[data-save-btn]')?.click();
        } else if (e.key === 'Escape') {
            window.location.href = @json(route('admin.products.index'));
        }
    });
})();
</script>
@endsection
