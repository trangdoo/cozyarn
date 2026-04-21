@extends('layouts.public')

@section('title', $post['title'] . ' — Blog CozyYarn')

@section('content')
<article class="blog-article">
    <div class="blog-article__inner">

        <nav class="policy-breadcrumb" aria-label="breadcrumb">
            <a href="/">Trang chủ</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('blog.index') }}">Blog</a>
            <span aria-hidden="true">›</span>
            @if($category)
                <a href="{{ route('blog.index', ['category' => $category['slug']]) }}">{{ $category['name'] }}</a>
                <span aria-hidden="true">›</span>
            @endif
            <span class="policy-breadcrumb__current">{{ $post['title'] }}</span>
        </nav>

        <header class="blog-article__head">
            @if($category)
                <span class="blog-cat-chip" style="--cat-color: {{ $category['color'] }}">
                    {{ $category['name'] }}
                </span>
            @endif
            <h1 class="blog-article__title">{{ $post['title'] }}</h1>
            <p class="blog-article__lead">{{ $post['excerpt'] }}</p>
            <div class="blog-article__meta">
                <div class="blog-article__author">
                    <div class="blog-article__author-avatar">
                        {{ mb_strtoupper(mb_substr($post['author'], 0, 1)) }}
                    </div>
                    <div>
                        <strong>{{ $post['author'] }}</strong>
                        <small>
                            {{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d/m/Y') }}
                            · {{ $post['read_time'] }} phút đọc
                        </small>
                    </div>
                </div>

                {{-- Tim action --}}
                <div class="blog-actions">
                    @auth
                        <form method="POST" action="{{ route('blog.like', ['slug' => $post['slug']]) }}" class="blog-actions__form">
                            @csrf
                            <button type="submit" class="blog-action blog-action--like @if($isLiked) is-active @endif"
                                    aria-label="{{ $isLiked ? 'Bỏ tim' : 'Thả tim' }}">
                                <svg viewBox="0 0 24 24" fill="@if($isLiked) currentColor @else none @endif" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M12 21s-7-4.5-9.5-9A5 5 0 0 1 12 6a5 5 0 0 1 9.5 6C19 16.5 12 21 12 21z" stroke-linejoin="round"/>
                                </svg>
                                <span>{{ $likeCount }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="blog-action blog-action--guest" title="Đăng nhập để thả tim">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 21s-7-4.5-9.5-9A5 5 0 0 1 12 6a5 5 0 0 1 9.5 6C19 16.5 12 21 12 21z" stroke-linejoin="round"/>
                            </svg>
                            <span>{{ $likeCount }}</span>
                        </a>
                    @endauth
                </div>
            </div>

            @if(session('cart_flash'))
                <div class="blog-flash">{{ session('cart_flash') }}</div>
            @endif
        </header>

        <div class="blog-article__cover">
            <img src="{{ $post['cover'] }}" alt="{{ $post['title'] }}">
        </div>

        <div class="blog-article__body">
            @foreach($post['sections'] as $section)
                <section class="blog-section">
                    <h2>{{ $section['heading'] }}</h2>
                    {!! $section['body'] !!}
                </section>
            @endforeach
        </div>

        @if(!empty($post['tags']))
            <div class="blog-article__tags">
                <strong>Tags:</strong>
                @foreach($post['tags'] as $tag)
                    <span class="blog-tag">#{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        {{-- Related posts --}}
        @if(count($related) > 0)
            <section class="blog-related">
                <h2 class="blog-related__title">Bài viết liên quan</h2>
                <div class="blog-grid blog-grid--compact">
                    @foreach($related as $r)
                        @php $rCat = $categories[$r['category']] ?? null; @endphp
                        <article class="blog-card" data-reveal>
                            <a href="{{ route('blog.show', ['slug' => $r['slug']]) }}" class="blog-card__link">
                                <div class="blog-card__image">
                                    <img src="{{ $r['cover'] }}" alt="{{ $r['title'] }}" loading="lazy">
                                </div>
                                <div class="blog-card__body">
                                    @if($rCat)
                                        <span class="blog-cat-chip blog-cat-chip--sm" style="--cat-color: {{ $rCat['color'] }}">
                                            {{ $rCat['name'] }}
                                        </span>
                                    @endif
                                    <h3 class="blog-card__title">{{ $r['title'] }}</h3>
                                    <div class="blog-meta blog-meta--sm">
                                        <span>{{ \Carbon\Carbon::parse($r['date'])->translatedFormat('d/m/Y') }}</span>
                                        <span class="blog-meta__dot">•</span>
                                        <span>{{ $r['read_time'] }} phút</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="blog-article__back">
            <a href="{{ route('blog.index') }}">← Quay lại tất cả bài viết</a>
        </div>

    </div>
</article>
@endsection
