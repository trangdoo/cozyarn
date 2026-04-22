@extends('layouts.admin')

@section('title', $post['title'] . ' — Xem bài viết')
@section('page_title', 'Chi tiết bài viết')

@php
    $active = 'blog';
    $featuredList = session('admin_blogs_featured', []);
    $isFeatured = ($post['featured'] ?? false) || \in_array($post['slug'], $featuredList, true);
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.blog.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $post['title'] }}</h1>
            <p>
                @if($isScheduled)
                    <span class="admin-sched-badge">⏱ Hẹn đăng {{ \Carbon\Carbon::parse($post['publish_at'])->format('H:i · d/m/Y') }}</span>
                @else
                    Đã đăng {{ \Carbon\Carbon::parse($post['publish_at'] ?? $post['date'])->format('d/m/Y') }}
                @endif
                · {{ $category['name'] ?? $post['category'] }} · {{ $post['read_time'] ?? 5 }} phút đọc
            </p>
        </div>
        <div class="admin-page__actions">
            @unless($isScheduled)
                <a href="{{ route('blog.show', $post['slug']) }}" target="_blank" class="admin-btn admin-btn--ghost">↗ Xem public</a>
            @endunless
            <a href="{{ route('admin.blog.edit', $post['slug']) }}" class="admin-btn admin-btn--primary">✎ Sửa</a>
        </div>
    </div>

    <div class="admin-grid-2">
        <section class="admin-card">
            <header class="admin-card__head"><h2>Bài viết</h2></header>
            @if(!empty($post['cover']))
                <div class="admin-show__image"><img src="{{ $post['cover'] }}" alt=""></div>
            @endif
            <div class="admin-show__desc">
                <p><strong>{{ $post['excerpt'] }}</strong></p>
                <hr style="border:none;border-top:1px solid #f5d6e3;margin:14px 0">
                <div class="admin-blog-body">
                    @foreach($post['sections'] ?? [] as $s)
                        @if(!empty($s['heading']))
                            <h3>{{ $s['heading'] }}</h3>
                        @endif
                        {!! $s['body'] ?? '' !!}
                    @endforeach
                </div>
            </div>
        </section>

        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin</h2></header>
            <ul class="admin-info">
                <li><span>Slug</span><strong><code class="admin-code">{{ $post['slug'] }}</code></strong></li>
                <li><span>Tác giả</span><strong>{{ $post['author'] ?? '—' }}</strong></li>
                <li><span>Danh mục</span><strong>{{ $category['name'] ?? $post['category'] }}</strong></li>
                <li><span>Ngày đăng</span><strong>{{ \Carbon\Carbon::parse($post['date'])->format('d/m/Y') }}</strong></li>
                @if(!empty($post['publish_at']))
                    <li><span>Publish lúc</span><strong style="{{ $isScheduled ? 'color:#b15e1f' : '' }}">{{ \Carbon\Carbon::parse($post['publish_at'])->format('d/m/Y H:i') }}</strong></li>
                @endif
                <li><span>Thời gian đọc</span><strong>{{ $post['read_time'] ?? 5 }} phút</strong></li>
                <li><span>Nổi bật</span><strong>{{ $isFeatured ? '★ Có' : '—' }}</strong></li>
            </ul>

            @if(!empty($post['tags']))
                <header class="admin-card__head"><h2>Tags</h2></header>
                <div style="padding: 14px 20px;">
                    @foreach($post['tags'] as $t)
                        <span class="admin-tag-mini">#{{ $t }}</span>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
