@extends('layouts.admin')

@php
    $isEdit = !empty($category);
    $action = $isEdit ? route('admin.categories.update', $category['slug']) : route('admin.categories.store');
@endphp

@section('title', ($isEdit ? 'Sửa' : 'Thêm') . ' danh mục — CozyYarn')
@section('page_title', $isEdit ? 'Sửa danh mục' : 'Thêm danh mục')

@php $active = 'categories'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.categories.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $isEdit ? 'Sửa danh mục' : 'Thêm danh mục' }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="admin-card admin-form">
        @csrf
        @if($isEdit) @method('PATCH') @endif

        @if($errors->any())
            <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
        @endif

        <label>Tên danh mục *
            <input type="text" name="name" required value="{{ old('name', $category['name'] ?? '') }}">
        </label>
        <label>Mô tả
            <textarea name="description" rows="3" maxlength="500">{{ old('description', $category['description'] ?? '') }}</textarea>
        </label>
        <label>Ảnh đại diện (URL)
            <input type="text" name="image" value="{{ old('image', $category['image'] ?? '') }}" placeholder="/images/1.jpg">
        </label>

        <div class="admin-form__actions">
            <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary">{{ $isEdit ? 'Lưu' : 'Tạo danh mục' }}</button>
        </div>
    </form>
</div>
@endsection
