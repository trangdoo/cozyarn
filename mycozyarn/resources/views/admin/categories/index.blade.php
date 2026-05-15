@extends('layouts.admin')

@section('title', 'Danh mục — CozyYarn')
@section('page_title', 'Danh mục sản phẩm')

@php $active = 'categories'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Danh mục</h1>
            <p>{{ $categories->total() }} danh mục</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn--primary">＋ Thêm danh mục</a>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm theo tên / slug / mô tả...">
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    <div class="admin-grid-3">
        @forelse($categories as $c)
            <article class="admin-card admin-cat-card">
                @if(!empty($c->image))
                    <img src="{{ $c->image }}" alt="{{ $c->name }}">
                @else
                    <div class="admin-cat-card__placeholder">🧶</div>
                @endif
                <div class="admin-cat-card__body">
                    <strong>{{ $c->name }}</strong>
                    <small>/{{ $c->slug }} · {{ $c->products_count ?? 0 }} sản phẩm</small>
                    @if(!empty($c->description))
                        <p>{{ Str::limit($c->description, 120) }}</p>
                    @endif
                </div>
                <div class="admin-cat-card__actions">
                    <a href="{{ route('admin.categories.edit', $c->slug) }}" class="admin-btn admin-btn--ghost">Sửa</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $c->slug) }}"
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

    @if($categories->hasPages())
        {{ $categories->links('vendor.pagination.cozy') }}
    @endif
</div>
@endsection
