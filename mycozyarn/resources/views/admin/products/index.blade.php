@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm — CozyYarn')
@section('page_title', 'Sản phẩm')

@php $active = 'products'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Sản phẩm</h1>
            <p>Đang có {{ count($products) }} sản phẩm</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn--primary">＋ Thêm sản phẩm</a>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm theo tên...">
        <select name="category">
            <option value="all" @selected($filter['category'] === 'all')>Tất cả danh mục</option>
            @foreach($categories as $slug => $c)
                <option value="{{ $slug }}" @selected($filter['category'] === $slug)>{{ $c['name'] }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            <option value="active" @selected($filter['status'] === 'active')>Đang bán</option>
            <option value="inactive" @selected($filter['status'] === 'inactive')>Ngưng bán</option>
        </select>
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    <div class="admin-card">
        <table class="admin-table admin-table--full">
            <thead>
                <tr><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Kho</th><th>Trạng thái</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    @php $stock = array_sum(array_column($p['variants'] ?? [], 'stock')); @endphp
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-user-cell__thumb">
                                    <img src="{{ $p['image'] ?? '/images/1.jpg' }}" alt="">
                                </div>
                                <div>
                                    <strong>{{ $p['name'] }}</strong>
                                    <small>{{ $p['slug'] }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $categories[$p['category_slug']]['name'] ?? $p['category_slug'] }}</td>
                        <td>
                            <strong>{{ number_format($p['price'], 0, ',', '.') }}₫</strong>
                            @if(!empty($p['oldPrice']))
                                <small style="text-decoration:line-through;color:#b09aa4">{{ number_format($p['oldPrice'], 0, ',', '.') }}₫</small>
                            @endif
                        </td>
                        <td>{{ $stock }}</td>
                        <td><span class="admin-badge admin-badge--{{ $p['status'] ?? 'active' }}">{{ ($p['status'] ?? 'active') === 'active' ? 'Đang bán' : 'Ngưng' }}</span></td>
                        <td class="admin-table__actions">
                            <a href="{{ route('admin.products.edit', ['category' => $p['category_slug'], 'slug' => $p['slug']]) }}" class="admin-btn admin-btn--ghost">Sửa</a>
                            <form method="POST" action="{{ route('admin.products.destroy', ['category' => $p['category_slug'], 'slug' => $p['slug']]) }}"
                                  onsubmit="return confirm('Xoá sản phẩm này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn--danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty"><p>Không có sản phẩm.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
