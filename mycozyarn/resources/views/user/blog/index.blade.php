@extends('layouts.public')

@section('title', 'Blog CozyYarn — Hướng dẫn, cảm hứng và câu chuyện đan móc')

@section('content')
<section class="blog-page">
    <div class="blog-page__inner">

        <header class="blog-hero">
            <span class="section-chip">Blog CozyYarn</span>
            <h1 class="blog-hero__title">Nơi những sợi len kể chuyện</h1>
            <p class="blog-hero__sub">
                Hướng dẫn kỹ thuật, mẫu đan đẹp, kinh nghiệm chăm sóc đồ len và câu chuyện của những người mê handmade.
            </p>
        </header>

        {{-- Featured post --}}
        @if($featured && !$activeCategory)
            @php
                $featCat = $categories[$featured['category']] ?? null;
            @endphp
            <a href="{{ route('blog.show', ['slug' => $featured['slug']]) }}" class="blog-featured" data-reveal>
                <div class="blog-featured__image">
                    <img src="{{ $featured['cover'] }}" alt="{{ $featured['title'] }}">
                    <span class="blog-featured__badge">Bài nổi bật</span>
                </div>
                <div class="blog-featured__body">
                    @if($featCat)
                        <span class="blog-cat-chip" style="--cat-color: {{ $featCat['color'] }}">
                            {{ $featCat['name'] }}
                        </span>
                    @endif
                    <h2>{{ $featured['title'] }}</h2>
                    <p>{{ $featured['excerpt'] }}</p>
                    <div class="blog-meta">
                        <span>{{ $featured['author'] }}</span>
                        <span class="blog-meta__dot">•</span>
                        <span>{{ \Carbon\Carbon::parse($featured['date'])->translatedFormat('d/m/Y') }}</span>
                        <span class="blog-meta__dot">•</span>
                        <span>{{ $featured['read_time'] }} phút đọc</span>
                    </div>
                </div>
            </a>
        @endif

        <div class="blog-layout">
            {{-- Sidebar: categories + popular --}}
            <aside class="blog-sidebar">
                <div class="blog-sidebar__card">
                    <h3>Danh mục</h3>
                    <ul class="blog-cat-list">
                        <li>
                            <a href="{{ route('blog.index') }}"
                               class="blog-cat-link @if(!$activeCategory) is-active @endif">
                                Tất cả bài viết
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li>
                                <a href="{{ route('blog.index', ['category' => $cat['slug']]) }}"
                                   class="blog-cat-link @if($activeCategory === $cat['slug']) is-active @endif"
                                   style="--cat-color: {{ $cat['color'] }}">
                                    <span class="blog-cat-dot"></span>
                                    {{ $cat['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="blog-sidebar__card blog-sidebar__card--promo">
                    <h3>Ưu đãi người đọc blog</h3>
                    <p>Nhập mã <strong>BLOGCOZY</strong> giảm 10% cho đơn đầu tiên dành cho bạn đọc blog thân thiết.</p>
                    <a href="/shop" class="cart-btn cart-btn--primary">Mua sắm ngay</a>
                </div>
            </aside>

            {{-- Main posts grid --}}
            <main class="blog-main">
                <div class="blog-main__head">
                    @if($activeCategory && isset($categories[$activeCategory]))
                        <h2 class="blog-main__title">Bài viết · {{ $categories[$activeCategory]['name'] }}</h2>
                        <p class="blog-main__count">{{ count($posts) }} bài viết</p>
                    @else
                        <h2 class="blog-main__title">Bài viết mới nhất</h2>
                        <p class="blog-main__count">{{ count($posts) }} bài</p>
                    @endif
                </div>

                @if(count($posts) === 0)
                    <div class="cart-empty">
                        <h3>Chưa có bài viết nào</h3>
                        <p>Danh mục này chưa có bài viết. Quay lại trang blog chính để xem các bài khác.</p>
                        <a href="{{ route('blog.index') }}" class="cart-btn cart-btn--primary">Về danh sách chính</a>
                    </div>
                @else
                    <div class="blog-grid">
                        @foreach($posts as $post)
                            @php $cat = $categories[$post['category']] ?? null; @endphp
                            <article class="blog-card" data-reveal>
                                <a href="{{ route('blog.show', ['slug' => $post['slug']]) }}" class="blog-card__link">
                                    <div class="blog-card__image">
                                        <img src="{{ $post['cover'] }}" alt="{{ $post['title'] }}" loading="lazy">
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
                                            <span>{{ $post['author'] }}</span>
                                            <span class="blog-meta__dot">•</span>
                                            <span>{{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d/m/Y') }}</span>
                                            <span class="blog-meta__dot">•</span>
                                            <span>{{ $post['read_time'] }} phút</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>
</section>
@endsection
