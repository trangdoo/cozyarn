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
            </div>
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

        <div class="blog-article__share">
            <span>Thấy bài viết hay? Chia sẻ với bạn bè nhé!</span>
            <div class="blog-share-btns">
                <button type="button" class="blog-share-btn" data-copy-url aria-label="Sao chép link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M10 14a4 4 0 0 1 0-5l3-3a4 4 0 0 1 6 6l-2 2"/>
                        <path d="M14 10a4 4 0 0 1 0 5l-3 3a4 4 0 0 1-6-6l2-2"/>
                    </svg>
                    <span data-copy-label>Sao chép link</span>
                </button>
            </div>
        </div>

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

<script>
(() => {
    const btn = document.querySelector('[data-copy-url]');
    if (!btn) return;
    const label = btn.querySelector('[data-copy-label]');
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(window.location.href);
            const old = label.textContent;
            label.textContent = 'Đã sao chép!';
            btn.classList.add('is-copied');
            setTimeout(() => {
                label.textContent = old;
                btn.classList.remove('is-copied');
            }, 1800);
        } catch (e) {
            label.textContent = 'Lỗi';
        }
    });
})();
</script>
@endsection
