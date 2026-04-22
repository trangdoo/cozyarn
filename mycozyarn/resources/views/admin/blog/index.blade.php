@extends('layouts.admin')

@section('title', 'Quản lý blog — CozyYarn')
@section('page_title', 'Blog')

@php
    $active = 'blog';
    $featuredList = session('admin_blogs_featured', []);
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Bài viết blog</h1>
            <p>{{ count($posts) }} bài viết</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" class="admin-btn admin-btn--primary">＋ Viết bài mới</a>
    </div>

    <div class="admin-card">
        <table class="admin-table admin-table--full">
            <thead>
                <tr><th>Bài viết</th><th>Danh mục</th><th>Tác giả</th><th>Ngày đăng</th><th>Nổi bật</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($posts as $p)
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-user-cell__thumb">
                                    <img src="{{ $p['cover'] ?? '/images/1.jpg' }}" alt="">
                                </div>
                                <div>
                                    <strong>{{ $p['title'] }}</strong>
                                    <small>{{ Str::limit($p['excerpt'] ?? '', 90) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $categories[$p['category']]['name'] ?? $p['category'] }}</td>
                        <td>{{ $p['author'] ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p['date'])->format('d/m/Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.blog.featured', $p['slug']) }}">
                                @csrf
                                @php $isFeatured = ($p['featured'] ?? false) || \in_array($p['slug'], $featuredList, true); @endphp
                                <button type="submit" class="admin-star @if($isFeatured) is-on @endif" aria-label="Toggle nổi bật">★</button>
                            </form>
                        </td>
                        <td class="admin-table__actions">
                            <a href="{{ route('blog.show', $p['slug']) }}" target="_blank" class="admin-btn admin-btn--ghost">Xem</a>
                            <a href="{{ route('admin.blog.edit', $p['slug']) }}" class="admin-btn admin-btn--ghost">Sửa</a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $p['slug']) }}"
                                  onsubmit="return confirm('Xoá bài viết này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn--danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty"><p>Chưa có bài viết.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
