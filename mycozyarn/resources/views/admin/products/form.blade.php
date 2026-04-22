@extends('layouts.admin')

@php
    $isEdit = !empty($product);
    $action = $isEdit
        ? route('admin.products.update', ['category' => $product['category_slug'], 'slug' => $product['slug']])
        : route('admin.products.store');
    $method = $isEdit ? 'PATCH' : 'POST';
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

    <form method="POST" action="{{ $action }}" class="admin-card admin-form">
        @csrf
        @if($isEdit) @method('PATCH') @endif

        @if($errors->any())
            <div class="admin-errors">
                @foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="admin-form__row">
            <label style="flex:2">Tên sản phẩm *
                <input type="text" name="name" required value="{{ old('name', $product['name'] ?? '') }}">
            </label>
            <label>Danh mục *
                <select name="category_slug" required>
                    @foreach($categories as $slug => $c)
                        <option value="{{ $slug }}" @selected(old('category_slug', $product['category_slug'] ?? '') === $slug)>{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <label>Mô tả ngắn *
            <input type="text" name="shortDesc" required maxlength="500" value="{{ old('shortDesc', $product['shortDesc'] ?? '') }}">
        </label>

        <label>Mô tả chi tiết
            <textarea name="desc" rows="4" maxlength="3000">{{ old('desc', $product['desc'] ?? '') }}</textarea>
        </label>

        <div class="admin-form__row">
            <label>Giá bán (₫) *
                <input type="number" name="price" required min="0" value="{{ old('price', $product['price'] ?? '') }}">
            </label>
            <label>Giá gốc (₫)
                <input type="number" name="oldPrice" min="0" value="{{ old('oldPrice', $product['oldPrice'] ?? '') }}">
            </label>
            <label>Tag
                <input type="text" name="tag" value="{{ old('tag', $product['tag'] ?? '') }}" placeholder="Hot, Mới, Sale...">
            </label>
        </div>

        <div class="admin-form__row">
            <label style="flex:2">Ảnh (URL)
                <input type="text" name="image" value="{{ old('image', $product['image'] ?? '/images/1.jpg') }}">
            </label>
            <label>Trạng thái *
                <select name="status" required>
                    <option value="active" @selected(old('status', $product['status'] ?? 'active') === 'active')>Đang bán</option>
                    <option value="inactive" @selected(old('status', $product['status'] ?? '') === 'inactive')>Ngưng bán</option>
                </select>
            </label>
        </div>

        <div class="admin-form__actions">
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo sản phẩm' }}</button>
        </div>
    </form>
</div>
@endsection
