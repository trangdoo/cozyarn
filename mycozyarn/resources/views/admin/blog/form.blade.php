@extends('layouts.admin')

@php
    $isEdit = !empty($post);
    $action = $isEdit ? route('admin.blog.update', $post['slug']) : route('admin.blog.store');
@endphp

@section('title', ($isEdit ? 'Sửa' : 'Viết') . ' bài — CozyYarn')
@section('page_title', $isEdit ? 'Sửa bài viết' : 'Viết bài mới')

@php $active = 'blog'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.blog.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $isEdit ? 'Sửa bài viết' : 'Viết bài mới' }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="admin-card admin-form">
        @csrf
        @if($isEdit) @method('PATCH') @endif

        @if($errors->any())
            <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
        @endif

        <label>Tiêu đề *
            <input type="text" name="title" required value="{{ old('title', $post['title'] ?? '') }}">
        </label>

        <label>Tóm tắt * <small>(1-2 câu hiện ở card)</small>
            <textarea name="excerpt" rows="2" required maxlength="500">{{ old('excerpt', $post['excerpt'] ?? '') }}</textarea>
        </label>

        <div class="admin-form__row">
            <label>Danh mục *
                <select name="category" required>
                    @foreach($categories as $slug => $c)
                        <option value="{{ $slug }}" @selected(old('category', $post['category'] ?? '') === $slug)>{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>Thời gian đọc (phút) *
                <input type="number" name="read_time" required min="1" max="60" value="{{ old('read_time', $post['read_time'] ?? 5) }}">
            </label>
            <label>Ngày đăng
                <input type="date" name="date" value="{{ old('date', $post['date'] ?? now()->toDateString()) }}">
            </label>
        </div>

        <div class="admin-form__row">
            <label style="flex:2">Ảnh cover (URL)
                <input type="text" name="cover" value="{{ old('cover', $post['cover'] ?? '/images/1.jpg') }}">
            </label>
            <label>Tác giả
                <input type="text" name="author" value="{{ old('author', $post['author'] ?? auth()->user()->name) }}">
            </label>
        </div>

        <label>Tags <small>(cách nhau dấu phẩy: len, cotton, hướng dẫn)</small>
            <input type="text" name="tags_raw" value="{{ old('tags_raw', isset($post['tags']) ? implode(', ', $post['tags']) : '') }}">
        </label>

        <label class="admin-checkbox">
            <input type="checkbox" name="featured" value="1" @checked(old('featured', $post['featured'] ?? false))>
            <span>Đặt làm bài nổi bật</span>
        </label>

        <div class="admin-form__actions">
            <a href="{{ route('admin.blog.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary">{{ $isEdit ? 'Lưu' : 'Đăng bài' }}</button>
        </div>
    </form>
</div>
@endsection
