@extends('layouts.admin')

@section('title', 'Danh mục — CozyYarn')
@section('page_title', 'Danh mục sản phẩm')

@php $active = 'categories'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Danh mục</h1>
            <p>{{ count($categories) }} danh mục</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn--primary">＋ Thêm danh mục</a>
    </div>

    <div class="admin-grid-3">
        @forelse($categories as $slug => $c)
            <article class="admin-card admin-cat-card">
                @if(!empty($c['image']))
                    <img src="{{ $c['image'] }}" alt="{{ $c['name'] }}">
                @else
                    <div class="admin-cat-card__placeholder">🧶</div>
                @endif
                <div class="admin-cat-card__body">
                    <strong>{{ $c['name'] }}</strong>
                    <small>/{{ $slug }}</small>
                    @if(!empty($c['description']))
                        <p>{{ Str::limit($c['description'], 120) }}</p>
                    @endif
                </div>
                <div class="admin-cat-card__actions">
                    <a href="{{ route('admin.categories.edit', $slug) }}" class="admin-btn admin-btn--ghost">Sửa</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $slug) }}"
                          onsubmit="return confirm('Xoá danh mục này?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn--danger">Xoá</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="admin-empty"><p>Chưa có danh mục nào.</p></div>
        @endforelse
    </div>
</div>
@endsection
