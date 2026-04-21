@extends('layouts.public')

@section('title', $category['name'] . ' · Shop CozyYarn')

@section('content')
<section class="shop-hero shop-hero--cat">
    <div class="shop-hero__deco shop-hero__deco--1" aria-hidden="true"></div>

    <div class="shop-hero__inner">
        <nav class="policy-breadcrumb" aria-label="breadcrumb">
            <a href="/">Trang chủ</a>
            <span aria-hidden="true">›</span>
            <a href="/shop">Shop</a>
            <span aria-hidden="true">›</span>
            <span class="policy-breadcrumb__current">{{ $category['name'] }}</span>
        </nav>
        <span class="section-chip">{{ $category['tag'] }}</span>
        <h1 class="shop-hero__title">{{ $category['name'] }}</h1>
        <p class="shop-hero__sub">{{ $category['desc'] }}</p>
    </div>
</section>

<section class="shop-list">
    <div class="shop-list__inner">

        {{-- Sidebar categories --}}
        <aside class="shop-aside">
            <h3 class="shop-aside__title">Danh mục</h3>
            <ul class="shop-aside__list">
                @foreach($categories as $cat)
                    <li>
                        <a href="/shop/{{ $cat['slug'] }}"
                           class="@if($cat['slug'] === $category['slug']) is-active @endif">
                            <span>{{ $cat['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        {{-- Product grid --}}
        <div class="shop-list__main">
            <div class="shop-list__toolbar">
                <span class="shop-list__count"><span data-visible-count>{{ count($products) }}</span> sản phẩm</span>
                <div class="shop-list__filters">
                    <div class="shop-list__tag-filter" role="tablist" aria-label="Lọc theo tag">
                        <button type="button" class="shop-filter is-active" data-filter-tag="all" role="tab">Tất cả</button>
                        <button type="button" class="shop-filter" data-filter-tag="hot" role="tab">Hot</button>
                        <button type="button" class="shop-filter" data-filter-tag="sale" role="tab">Sale</button>
                        <button type="button" class="shop-filter" data-filter-tag="mới" role="tab">Mới</button>
                    </div>
                    <div class="shop-list__sort">
                        <label for="shop-sort">Sắp xếp:</label>
                        <select id="shop-sort" class="shop-sort" data-shop-sort>
                            <option value="default">Mới nhất</option>
                            <option value="price-asc">Giá tăng dần</option>
                            <option value="price-desc">Giá giảm dần</option>
                            <option value="sale">% Giảm giá</option>
                            <option value="name">Tên A → Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="product-grid" data-product-grid>
                @foreach($products as $idx => $p)
                    @php
                        $salePct = $p['oldPrice'] ? round(100 - ($p['price'] / $p['oldPrice']) * 100) : 0;
                        $tagKey  = $p['tag'] ? mb_strtolower($p['tag']) : '';
                    @endphp
                    <article class="product-card"
                             data-reveal
                             data-idx="{{ $idx }}"
                             data-price="{{ $p['price'] }}"
                             data-sale="{{ $salePct }}"
                             data-name="{{ $p['name'] }}"
                             data-tag="{{ $tagKey }}">
                        <a class="product-card__link" href="/shop/{{ $category['slug'] }}/{{ $p['slug'] }}">
                            <div class="product-card__img">
                                <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}">
                                @if($p['tag'])
                                    <span class="product-card__tag product-card__tag--{{ strtolower($p['tag']) }}">{{ $p['tag'] }}</span>
                                @endif
                                @if($p['oldPrice'])
                                    <span class="product-card__sale">
                                        -{{ round(100 - ($p['price'] / $p['oldPrice']) * 100) }}%
                                    </span>
                                @endif
                            </div>
                            <div class="product-card__body">
                                <h3 class="product-card__name">{{ $p['name'] }}</h3>
                                <p class="product-card__desc">{{ $p['shortDesc'] }}</p>
                                <div class="product-card__colors">
                                    @foreach(array_slice($p['variants'], 0, 5) as $v)
                                        <span class="color-dot" style="background: {{ $v['color'] }}" title="{{ $v['label'] }}"></span>
                                    @endforeach
                                    @if(count($p['variants']) > 5)
                                        <span class="color-dot color-dot--more">+{{ count($p['variants']) - 5 }}</span>
                                    @endif
                                </div>
                                <div class="product-card__footer">
                                    <div class="product-card__price">
                                        <span class="price-now">{{ number_format($p['price'], 0, ',', '.') }} ₫</span>
                                        @if($p['oldPrice'])
                                            <span class="price-old">{{ number_format($p['oldPrice'], 0, ',', '.') }} ₫</span>
                                        @endif
                                    </div>
                                    <span class="product-card__btn" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            @if(empty($products))
                <div class="shop-empty">Danh mục này sắp có thêm sản phẩm — hãy quay lại sau nhé!</div>
            @endif
            <div class="shop-empty" data-empty-state hidden>Không có sản phẩm phù hợp với bộ lọc — thử chọn lại nhé!</div>
        </div>
    </div>
</section>

<script>
(function () {
    const grid      = document.querySelector('[data-product-grid]');
    const sortSel   = document.querySelector('[data-shop-sort]');
    const filterBtns = document.querySelectorAll('[data-filter-tag]');
    const countEl   = document.querySelector('[data-visible-count]');
    const emptyEl   = document.querySelector('[data-empty-state]');
    if (!grid) return;

    const originalOrder = Array.from(grid.children);
    let activeFilter = 'all';

    function apply() {
        // Filter
        const cards = originalOrder.slice();
        const visible = cards.filter(c => {
            if (activeFilter === 'all') return true;
            return c.dataset.tag === activeFilter;
        });

        // Sort
        const mode = sortSel ? sortSel.value : 'default';
        visible.sort((a, b) => {
            switch (mode) {
                case 'price-asc':  return +a.dataset.price - +b.dataset.price;
                case 'price-desc': return +b.dataset.price - +a.dataset.price;
                case 'sale':       return +b.dataset.sale  - +a.dataset.sale;
                case 'name':       return a.dataset.name.localeCompare(b.dataset.name, 'vi');
                default:           return +a.dataset.idx - +b.dataset.idx;
            }
        });

        // Hide others, show sorted list in order
        const visibleSet = new Set(visible);
        cards.forEach(c => {
            if (!visibleSet.has(c)) c.style.display = 'none';
            else c.style.display = '';
        });
        visible.forEach(c => grid.appendChild(c));

        // Update count + empty state
        if (countEl) countEl.textContent = visible.length;
        if (emptyEl) emptyEl.hidden = visible.length > 0;
    }

    if (sortSel) sortSel.addEventListener('change', apply);
    filterBtns.forEach(btn => btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        activeFilter = btn.dataset.filterTag;
        apply();
    }));
})();
</script>
@endsection
