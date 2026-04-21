@extends('layouts.public')

@section('title', 'Bài viết đã tim — CozyYarn')

@section('content')
<section class="acc-page">
    <div class="acc-page__inner">
        <div class="acc-page__head">
            <span class="section-chip">Tài khoản</span>
            <h1 class="acc-page__title">Bài viết đã tim</h1>
            <p class="acc-page__sub">
                @if(count($posts) > 0)
                    Bạn đã thả tim cho {{ count($posts) }} bài viết.
                @else
                    Chưa có bài viết nào được thả tim.
                @endif
            </p>
        </div>

        <div class="acc-layout">
            @include('user.account._sidebar', ['active' => 'blog_liked'])

            <div class="acc-content">
                @if(session('cart_flash'))
                    <div class="cart-alert cart-alert--success" style="margin-bottom:18px">{{ session('cart_flash') }}</div>
                @endif

                @if(count($posts) === 0)
                    <div class="cart-empty">
                        <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                            <circle cx="60" cy="60" r="52" fill="#fde4ee"/>
                            <path d="M60 88s-22-13-22-32a15 15 0 0 1 22-13 15 15 0 0 1 22 13c0 19-22 32-22 32z" stroke="#d97b9d" stroke-width="3" fill="none" stroke-linejoin="round"/>
                        </svg>
                        <h3>Chưa có bài viết nào</h3>
                        <p>Khi đọc bài trên Blog, bấm nút <strong>Thả tim ♡</strong> để lưu lại những bài bạn thích.</p>
                        <a href="{{ route('blog.index') }}" class="cart-btn cart-btn--primary">Đi đến Blog</a>
                    </div>
                @else
                    <div class="blog-grid">
                        @foreach($posts as $post)
                            @php
                                $cat = $categories[$post['category']] ?? null;
                                $seed = \crc32($post['slug'] . '|like');
                                $likes = 18 + $seed % 120 + 1;
                            @endphp
                            <article class="blog-card" data-reveal>
                                <a href="{{ route('blog.show', ['slug' => $post['slug']]) }}" class="blog-card__link">
                                    <div class="blog-card__image">
                                        <img src="{{ $post['cover'] }}" alt="{{ $post['title'] }}" loading="lazy">
                                        <span class="blog-card__liked-badge" title="Đã tim">
                                            <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M12 21s-7-4.5-9.5-9A5 5 0 0 1 12 6a5 5 0 0 1 9.5 6C19 16.5 12 21 12 21z" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                    <div class="blog-card__body">
                                        @if($cat)
                                            <span class="blog-cat-chip blog-cat-chip--sm" style="--cat-color: {{ $cat['color'] }}">
                                                {{ $cat['name'] }}
                                            </span>
                                        @endif
                                        <h3 class="blog-card__title">{{ $post['title'] }}</h3>
                                        <p class="blog-card__excerpt">{{ $post['excerpt'] }}</p>
                                        <div class="blog-meta blog-meta--sm">
                                            <span>{{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d/m/Y') }}</span>
                                            <span class="blog-meta__dot">•</span>
                                            <span class="blog-card__likes is-liked">
                                                <svg viewBox="0 0 16 16" fill="currentColor" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M8 14s-4.5-2.8-6-5.5A3 3 0 0 1 8 4a3 3 0 0 1 6 4.5c-1.5 2.7-6 5.5-6 5.5z" stroke-linejoin="round"/></svg>
                                                {{ $likes }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                <form method="POST" action="{{ route('blog.like', ['slug' => $post['slug']]) }}" class="blog-card__unsave">
                                    @csrf
                                    <button type="submit" aria-label="Bỏ tim">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
                                        Bỏ tim
                                    </button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
