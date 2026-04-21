@extends('layouts.public')

@section('title', $product['name'] . ' · CozyYarn')

@section('content')
@php
    // ═════ Deterministic review data per product — dùng cho header + tab ═════
    $reviewData = require resource_path('reviews.php');
    $catPool    = $reviewData['pools'][$category['slug']] ?? $reviewData['pools']['len-soi'];
    $names      = $reviewData['names'];
    $text5      = $reviewData['text_5'];
    $text4      = $reviewData['text_4'];
    $text3      = $reviewData['text_3'];
    $text2      = $reviewData['text_2'];
    $text1      = $reviewData['text_1'];

    // 4 seed độc lập từ slug → mỗi sản phẩm ra stats & review riêng
    $seedAvg   = crc32($product['slug'] . '|avg');
    $seedTotal = crc32($product['slug'] . '|total');
    $seedSold  = crc32($product['slug'] . '|sold');
    $seedPick  = crc32($product['slug'] . '|pick');

    $avgOptions = [4.6, 4.7, 4.8, 4.9, 5.0];
    $avg = $avgOptions[$seedAvg % count($avgOptions)];

    // Total reviews = 24-63 (số review thực sẽ render, phù hợp để user xem hết)
    $totalReviews = 24 + $seedTotal % 40;

    // Sold count (cao hơn reviews vì không phải ai mua cũng review)
    $soldCount = 150 + $seedSold % 1500;
    $totalSold = $soldCount > 1000 ? '1K+' : floor($soldCount / 50) * 50 . '+';

    // Phân bố sao dựa trên avg
    $pct5 = $avg >= 4.95 ? 0.92 : ($avg >= 4.85 ? 0.84 : ($avg >= 4.75 ? 0.76 : ($avg >= 4.65 ? 0.68 : 0.58)));
    $rest = 1 - $pct5;
    $breakdown = [
        5 => (int) round($totalReviews * $pct5),
        4 => (int) round($totalReviews * $rest * 0.55),
        3 => (int) round($totalReviews * $rest * 0.25),
        2 => (int) round($totalReviews * $rest * 0.13),
        1 => (int) round($totalReviews * $rest * 0.07),
    ];
    // Điều chỉnh 5★ để tổng khớp
    $breakdown[5] += $totalReviews - array_sum($breakdown);

    // Danh sách sao theo thứ tự chronological (trộn nhẹ)
    $starsOrder = [];
    foreach ($breakdown as $s => $cnt) {
        for ($i = 0; $i < $cnt; $i++) $starsOrder[] = $s;
    }
    // Shuffle deterministic: dùng crc32 làm hash key để trộn đều
    $shuffled = [];
    foreach ($starsOrder as $i => $s) {
        $shuffled[] = ['s' => $s, 'k' => crc32($seedPick . '|' . $i)];
    }
    usort($shuffled, fn($a, $b) => $a['k'] <=> $b['k']);
    $starsOrder = array_column($shuffled, 's');

    // Sinh reviews
    $nameCount = count($names);
    $catCount  = count($catPool);
    $varCount  = count($product['variants']);
    $reviews   = [];

    foreach ($starsOrder as $i => $stars) {
        $nameInfo = $names[($seedPick + $i * 7) % $nameCount];
        $variant  = $product['variants'][($seedPick + $i * 11) % $varCount];

        // Chọn text theo sao
        if ($stars >= 5) {
            // Mix: nửa từ catPool (category-specific), nửa từ text_5 (generic premium)
            if ($i % 2 === 0) {
                $text = $catPool[($seedPick + $i * 5) % $catCount]['text'];
            } else {
                $text = $text5[($seedPick + $i * 3) % count($text5)];
            }
        } elseif ($stars === 4) {
            $text = $text4[($seedPick + $i * 3) % count($text4)];
        } elseif ($stars === 3) {
            $text = $text3[($seedPick + $i * 3) % count($text3)];
        } elseif ($stars === 2) {
            $text = $text2[($seedPick + $i * 3) % count($text2)];
        } else {
            $text = $text1[($seedPick + $i * 3) % count($text1)];
        }

        // Date giảm dần: review đầu = mới nhất
        $daysAgo = $i * 2 + ($seedPick + $i * 3) % 4;
        $timestamp = strtotime("-{$daysAgo} days");

        $reviews[] = [
            'name'     => $nameInfo['name'],
            'initials' => $nameInfo['initials'],
            'stars'    => $stars,
            'date'     => date('d/m/Y', $timestamp),
            'variant'  => $variant['label'],
            'text'     => $text,
            'likes'    => ($seedPick + $i * 19) % 40 + 2,
        ];
    }

    $previewCount = 5;

@endphp
<section class="product-page">
    <div class="product-page__inner">

        <nav class="policy-breadcrumb" aria-label="breadcrumb">
            <a href="/">Trang chủ</a>
            <span aria-hidden="true">›</span>
            <a href="/shop">Shop</a>
            <span aria-hidden="true">›</span>
            <a href="/shop/{{ $category['slug'] }}">{{ $category['name'] }}</a>
            <span aria-hidden="true">›</span>
            <span class="policy-breadcrumb__current">{{ $product['name'] }}</span>
        </nav>

        <div class="product-page__grid">

            {{-- Gallery --}}
            <div class="pd-gallery" data-reveal>
                <div class="pd-gallery__main">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" id="pdMainImage">
                    @if($product['tag'])
                        <span class="product-card__tag product-card__tag--{{ strtolower($product['tag']) }}">{{ $product['tag'] }}</span>
                    @endif
                </div>
                <div class="pd-gallery__thumbs">
                    @foreach(['1','2','3','4'] as $i)
                        <button type="button" class="pd-thumb @if($i === '1') is-active @endif" data-thumb="/images/{{ $i }}.jpg">
                            <img src="/images/{{ $i }}.jpg" alt="Thumb {{ $i }}">
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Info --}}
            <div class="pd-info" data-reveal>
                <h1 class="pd-name">{{ $product['name'] }}</h1>

                <div class="pd-rating">
                    <span class="pd-rating__stars">
                        @for($i = 1; $i <= 5; $i++){{ $i <= round($avg) ? '★' : '☆' }}@endfor
                    </span>
                    <span class="pd-rating__text">{{ $avg }} · {{ $totalReviews }} đánh giá</span>
                    <span class="pd-rating__dot">•</span>
                    <span class="pd-rating__sold">Đã bán {{ $totalSold }}</span>
                </div>

                <p class="pd-short">{{ $product['shortDesc'] }}</p>

                <div class="pd-price-wrap">
                    <span class="pd-price" id="pdPrice">{{ number_format($product['price'], 0, ',', '.') }} ₫</span>
                    @if($product['oldPrice'])
                        <span class="pd-price-old">{{ number_format($product['oldPrice'], 0, ',', '.') }} ₫</span>
                        <span class="pd-price-sale">-{{ round(100 - ($product['price'] / $product['oldPrice']) * 100) }}%</span>
                    @endif
                </div>

                {{-- Variants --}}
                <div class="pd-variants">
                    <div class="pd-variant-row">
                        <span class="pd-variant-label">Phân loại: <strong id="pdVariantName">{{ $product['variants'][0]['label'] ?? '' }}</strong></span>
                        <div class="pd-variant-options" role="radiogroup" aria-label="Chọn phân loại">
                            @foreach($product['variants'] as $idx => $v)
                                <button type="button"
                                        class="pd-swatch @if($idx === 0) is-selected @endif"
                                        data-variant='@json(["label" => $v["label"], "stock" => $v["stock"]])'
                                        data-color="{{ $v['color'] }}"
                                        title="{{ $v['label'] }} · Còn {{ $v['stock'] }}"
                                        role="radio"
                                        aria-checked="@if($idx === 0) true @else false @endif">
                                    <span class="pd-swatch__dot" style="background: {{ $v['color'] }}"></span>
                                    <span class="pd-swatch__label">{{ $v['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if(!empty($product['sizes']))
                        <div class="pd-variant-row">
                            <span class="pd-variant-label">Kích cỡ:</span>
                            <div class="pd-variant-options" role="radiogroup" aria-label="Chọn size">
                                @foreach($product['sizes'] as $idx => $s)
                                    <button type="button" class="pd-size @if($idx === 0) is-selected @endif" role="radio" aria-checked="@if($idx === 0) true @else false @endif">{{ $s }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="pd-variant-row">
                        <span class="pd-variant-label">Số lượng:</span>
                        <div class="pd-qty">
                            <button type="button" class="pd-qty__btn" data-qty="-1" aria-label="Giảm">−</button>
                            <input type="text" class="pd-qty__input" value="1" id="pdQtyInput" aria-label="Số lượng">
                            <button type="button" class="pd-qty__btn" data-qty="+1" aria-label="Tăng">+</button>
                        </div>
                        <span class="pd-stock" id="pdStock">Còn {{ $product['variants'][0]['stock'] ?? 0 }} sản phẩm</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('cart.add') }}" class="pd-actions" data-cart-form>
                    @csrf
                    <input type="hidden" name="category" value="{{ $category['slug'] }}">
                    <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                    <input type="hidden" name="name" value="{{ $product['name'] }}">
                    <input type="hidden" name="image" value="{{ $product['image'] }}">
                    <input type="hidden" name="price" value="{{ $product['price'] }}">
                    <input type="hidden" name="variant" value="{{ $product['variants'][0]['label'] ?? '' }}" data-cart-variant>
                    <input type="hidden" name="size"    value="{{ $product['sizes'][0] ?? '' }}" data-cart-size>
                    <input type="hidden" name="qty"     value="1" data-cart-qty>

                    <button type="submit" class="pd-btn pd-btn--cart">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="20" r="1.6" fill="currentColor"/><circle cx="18" cy="20" r="1.6" fill="currentColor"/><path d="M3 4h2l2.2 10.3a1 1 0 0 0 1 .7h9.8a1 1 0 0 0 1-.8L21 8H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Thêm vào giỏ
                    </button>
                    <button type="submit" class="pd-btn pd-btn--buy" formaction="{{ route('checkout.buyNow') }}">Mua ngay</button>
                    <button type="button" class="pd-btn pd-btn--wish" aria-label="Yêu thích">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 21s-7-4.5-9.5-9A5 5 0 0 1 12 6a5 5 0 0 1 9.5 6C19 16.5 12 21 12 21z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                    </button>
                </form>

                <ul class="pd-features">
                    @foreach($product['features'] as $feat)
                        <li>
                            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="7" fill="currentColor" opacity="0.15"/><path d="M5 8 L7 10 L11 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $feat }}
                        </li>
                    @endforeach
                </ul>

                {{-- Chat nhanh với shop về sản phẩm --}}
                @php
                    $productThreadId = 'product-' . $category['slug'] . '-' . $product['slug'];
                @endphp
                <div class="pd-chat">
                    <div class="pd-chat__head">
                        <div class="pd-chat__avatar">C</div>
                        <div class="pd-chat__info">
                            <strong>Chat với shop về sản phẩm này</strong>
                            <small>Phản hồi trong vài phút · {{ $product['name'] }}</small>
                        </div>
                    </div>
                    @auth
                        <form method="POST" action="{{ route('user.chat.send') }}" class="pd-chat__form">
                            @csrf
                            <input type="hidden" name="thread_id" value="{{ $productThreadId }}">
                            <input type="hidden" name="product[slug]"     value="{{ $product['slug'] }}">
                            <input type="hidden" name="product[category]" value="{{ $category['slug'] }}">
                            <input type="hidden" name="product[name]"     value="{{ $product['name'] }}">
                            <input type="hidden" name="product[image]"    value="{{ $product['image'] }}">
                            <input type="hidden" name="product[price]"    value="{{ $product['price'] }}">
                            <textarea name="content" rows="2" required maxlength="2000"
                                      placeholder="VD: Sản phẩm còn size S không ạ?"></textarea>
                            <div class="pd-chat__foot">
                                <a href="{{ route('user.chat.thread', ['threadId' => $productThreadId]) }}"
                                   class="pd-chat__history">Xem lịch sử chat</a>
                                <button type="submit" class="pd-btn pd-btn--buy pd-chat__send">Gửi tin nhắn</button>
                            </div>
                        </form>
                    @else
                        <div class="pd-chat__guest">
                            <p>Đăng nhập để chat với shop về sản phẩm này.</p>
                            <a href="{{ route('login') }}" class="pd-btn pd-btn--buy">Đăng nhập</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Description tabs --}}
        <div class="pd-tabs" data-reveal>
            <div class="pd-tabs__head" role="tablist">
                <button type="button" class="pd-tab is-active" data-tab="desc" role="tab">Mô tả</button>
                <button type="button" class="pd-tab" data-tab="spec" role="tab">Thông số</button>
                <button type="button" class="pd-tab" data-tab="reviews" role="tab">Đánh giá</button>
            </div>
            <div class="pd-tabs__body">
                {{-- Mô tả --}}
                <div class="pd-tab__panel is-active" data-panel="desc">
                    <div class="pd-desc">
                        <p class="pd-desc__lead">{{ $product['desc'] }}</p>

                        <h4 class="pd-desc__heading">
                            <span class="pd-desc__icon">✨</span> Điểm nổi bật
                        </h4>
                        <ul class="pd-desc__features">
                            @foreach($product['features'] as $feat)
                                <li>
                                    <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" fill="currentColor" opacity="0.15"/><path d="M6 10 l3 3 l5 -6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>{{ $feat }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <h4 class="pd-desc__heading">
                            <span class="pd-desc__icon">🧶</span> Gợi ý sử dụng
                        </h4>
                        <ul class="pd-desc__tips">
                            <li>Phù hợp cho cả người mới tập và thợ lâu năm.</li>
                            <li>Giặt tay với nước lạnh, phơi phẳng nơi thoáng mát — tránh vắt mạnh làm sợi rối.</li>
                            <li>Bảo quản trong túi zip hoặc hộp kín để tránh bụi và côn trùng.</li>
                            <li>Nên mua dư 1 cuộn để đảm bảo cùng lô màu cho dự án lớn.</li>
                        </ul>

                        <div class="pd-guarantees">
                            <div class="pd-guarantee">
                                <svg viewBox="0 0 32 32" fill="none"><path d="M16 4l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                <div>
                                    <strong>Chính hãng 100%</strong>
                                    <small>Len chọn lọc kỹ</small>
                                </div>
                            </div>
                            <div class="pd-guarantee">
                                <svg viewBox="0 0 32 32" fill="none"><rect x="3" y="10" width="17" height="12" rx="2" stroke="currentColor" stroke-width="2"/><path d="M20 13h4l5 4v5h-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="24" r="2.5" stroke="currentColor" stroke-width="2"/><circle cx="22" cy="24" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
                                <div>
                                    <strong>Giao 2–4 ngày</strong>
                                    <small>Toàn quốc</small>
                                </div>
                            </div>
                            <div class="pd-guarantee">
                                <svg viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2"/><path d="M11 16l4 4 6-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <div>
                                    <strong>Đổi trả 7 ngày</strong>
                                    <small>Miễn phí nếu lỗi</small>
                                </div>
                            </div>
                            <div class="pd-guarantee">
                                <svg viewBox="0 0 32 32" fill="none"><path d="M16 4v8m0 0l-4-4m4 4l4-4M6 16v12h20V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <div>
                                    <strong>Đóng gói kỹ</strong>
                                    <small>Như gói quà</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Thông số --}}
                <div class="pd-tab__panel" data-panel="spec">
                    <ul class="pd-spec">
                        <li><span>Danh mục</span><strong>{{ $category['name'] }}</strong></li>
                        <li><span>Thương hiệu</span><strong>CozyYarn</strong></li>
                        <li><span>Xuất xứ</span><strong>Việt Nam / Nhập khẩu</strong></li>
                        <li><span>Số phân loại</span><strong>{{ count($product['variants']) }} loại</strong></li>
                        @if(!empty($product['sizes']))
                            <li><span>Kích cỡ / trọng lượng</span><strong>{{ implode(' · ', $product['sizes']) }}</strong></li>
                        @endif
                        <li><span>Tổng tồn kho</span><strong>{{ array_sum(array_column($product['variants'], 'stock')) }}+ sản phẩm</strong></li>
                        <li><span>Chất liệu</span><strong>{{ $product['features'][0] ?? 'Cao cấp' }}</strong></li>
                        <li><span>Bảo quản</span><strong>Nơi khô ráo, tránh nắng trực tiếp</strong></li>
                        <li><span>Hướng dẫn giặt</span><strong>Giặt tay nước lạnh, phơi phẳng</strong></li>
                        <li><span>Chứng nhận</span><strong>An toàn cho da nhạy cảm</strong></li>
                        <li><span>Bảo hành</span><strong>Đổi trả 7 ngày nếu lỗi NSX</strong></li>
                    </ul>
                </div>

                {{-- Đánh giá --}}
                <div class="pd-tab__panel" data-panel="reviews">
                    <div class="pd-reviews">
                        <div class="pd-reviews__summary">
                            <div class="pd-score">
                                <div class="pd-score__big">{{ $avg }}</div>
                                <div class="pd-score__stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="@if($i <= round($avg)) is-on @endif">★</span>
                                    @endfor
                                </div>
                                <div class="pd-score__count">{{ $totalReviews }} đánh giá · Đã bán {{ $totalSold }}</div>
                            </div>
                            <div class="pd-score__bars">
                                @foreach([5, 4, 3, 2, 1] as $star)
                                    @php $pct = $totalReviews ? round($breakdown[$star] / $totalReviews * 100) : 0; @endphp
                                    <div class="pd-score__bar">
                                        <span class="pd-score__label">{{ $star }} ★</span>
                                        <span class="pd-score__track"><i style="width: {{ $pct }}%"></i></span>
                                        <span class="pd-score__num">{{ $breakdown[$star] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pd-reviews__filter">
                            <button type="button" class="pd-rv-tab is-active" data-rv-filter="all">Tất cả ({{ $totalReviews }})</button>
                            <button type="button" class="pd-rv-tab" data-rv-filter="5">5 ★ ({{ $breakdown[5] }})</button>
                            <button type="button" class="pd-rv-tab" data-rv-filter="4">4 ★ ({{ $breakdown[4] }})</button>
                            <button type="button" class="pd-rv-tab" data-rv-filter="3">3 ★ ({{ $breakdown[3] }})</button>
                            <button type="button" class="pd-rv-tab" data-rv-filter="image">Có ảnh</button>
                        </div>

                        <div class="pd-reviews__list" data-reviews-list data-preview="{{ $previewCount }}">
                            @foreach($reviews as $r)
                                <article class="pd-review" data-stars="{{ $r['stars'] }}">
                                    <div class="pd-review__avatar" style="background: hsl({{ ($loop->index * 47) % 360 }}, 65%, 85%); color: hsl({{ ($loop->index * 47) % 360 }}, 55%, 35%);">{{ $r['initials'] }}</div>
                                    <div class="pd-review__body">
                                        <div class="pd-review__head">
                                            <strong>{{ $r['name'] }}</strong>
                                            <span class="pd-review__stars">
                                                @for($i = 1; $i <= 5; $i++){{ $i <= $r['stars'] ? '★' : '☆' }}@endfor
                                            </span>
                                        </div>
                                        <div class="pd-review__meta">
                                            <span>{{ $r['date'] }}</span>
                                            <span class="pd-review__dot">•</span>
                                            <span>Phân loại: {{ $r['variant'] }}</span>
                                        </div>
                                        <p class="pd-review__text">{{ $r['text'] }}</p>
                                        <div class="pd-review__actions">
                                            <button type="button" class="pd-review__like">
                                                <svg viewBox="0 0 20 20" fill="none"><path d="M7 9l2.5-5a1.5 1.5 0 1 1 3 0v4h4a2 2 0 0 1 2 2l-1 5a2 2 0 0 1-2 2H7V9z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><rect x="2" y="9" width="5" height="8" rx="1" stroke="currentColor" stroke-width="1.6"/></svg>
                                                Hữu ích ({{ $r['likes'] }})
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="pd-reviews__more">
                            <span data-reviews-count>Đang hiển thị {{ $previewCount }} / {{ $totalReviews }} đánh giá</span>
                            <button type="button" class="pd-reviews__more-btn" data-reviews-toggle
                                    data-label-more="Xem thêm đánh giá"
                                    data-label-less="Thu gọn">
                                <span data-btn-label>Xem thêm đánh giá</span>
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>

                        {{-- CTA chỉ hiện cho guest — khuyến khích đăng nhập.
                             User đã đăng nhập viết đánh giá ở trang chi tiết đơn hàng đã giao. --}}
                        @guest
                            <div class="pd-reviews__cta pd-reviews__cta--locked">
                                <div class="pd-reviews__cta-icon">
                                    <svg viewBox="0 0 24 24" fill="none"><rect x="5" y="11" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 11V8a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <div class="pd-reviews__cta-body">
                                    <strong>Đăng nhập để viết đánh giá</strong>
                                    <p>Chỉ tài khoản đã <strong>mua</strong> và <strong>nhận hàng thành công</strong> mới có thể để lại đánh giá cho sản phẩm này.</p>
                                </div>
                                <a href="{{ route('login') }}" class="pd-btn pd-btn--buy pd-reviews__cta-btn">Đăng nhập</a>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        {{-- Related products --}}
        @if(!empty($related))
            <div class="pd-related" data-reveal>
                <h2 class="pd-related__title">Sản phẩm khác trong {{ $category['name'] }}</h2>
                <div class="product-grid product-grid--compact">
                    @foreach($related as $r)
                        <article class="product-card" data-reveal>
                            <a class="product-card__link" href="/shop/{{ $category['slug'] }}/{{ $r['slug'] }}">
                                <div class="product-card__img">
                                    <img src="{{ $r['image'] }}" alt="{{ $r['name'] }}">
                                    @if($r['tag'])
                                        <span class="product-card__tag product-card__tag--{{ strtolower($r['tag']) }}">{{ $r['tag'] }}</span>
                                    @endif
                                    @if($r['oldPrice'])
                                        <span class="product-card__sale">-{{ round(100 - ($r['price'] / $r['oldPrice']) * 100) }}%</span>
                                    @endif
                                </div>
                                <div class="product-card__body">
                                    <h3 class="product-card__name">{{ $r['name'] }}</h3>
                                    <div class="product-card__colors">
                                        @foreach(array_slice($r['variants'], 0, 4) as $v)
                                            <span class="color-dot" style="background: {{ $v['color'] }}" title="{{ $v['label'] }}"></span>
                                        @endforeach
                                        @if(count($r['variants']) > 4)
                                            <span class="color-dot color-dot--more">+{{ count($r['variants']) - 4 }}</span>
                                        @endif
                                    </div>
                                    <div class="product-card__footer">
                                        <div class="product-card__price">
                                            <span class="price-now">{{ number_format($r['price'], 0, ',', '.') }} ₫</span>
                                            @if($r['oldPrice'])
                                                <span class="price-old">{{ number_format($r['oldPrice'], 0, ',', '.') }} ₫</span>
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
            </div>
        @endif

    </div>
</section>

<script>
(function () {
    const swatches = document.querySelectorAll('.pd-swatch');
    const stockEl  = document.getElementById('pdStock');
    const nameEl   = document.getElementById('pdVariantName');
    const mainImg  = document.getElementById('pdMainImage');

    swatches.forEach(sw => sw.addEventListener('click', () => {
        swatches.forEach(s => { s.classList.remove('is-selected'); s.setAttribute('aria-checked', 'false'); });
        sw.classList.add('is-selected');
        sw.setAttribute('aria-checked', 'true');
        try {
            const data = JSON.parse(sw.dataset.variant);
            if (nameEl) nameEl.textContent = data.label;
            if (stockEl) stockEl.textContent = `Còn ${data.stock} sản phẩm`;
        } catch (e) {}
    }));

    document.querySelectorAll('.pd-size').forEach(el => {
        el.addEventListener('click', () => {
            el.parentElement.querySelectorAll('.pd-size').forEach(s => { s.classList.remove('is-selected'); s.setAttribute('aria-checked', 'false'); });
            el.classList.add('is-selected');
            el.setAttribute('aria-checked', 'true');
        });
    });

    const qtyInput = document.getElementById('pdQtyInput');
    document.querySelectorAll('.pd-qty__btn').forEach(b => b.addEventListener('click', () => {
        const delta = parseInt(b.dataset.qty, 10);
        let v = parseInt(qtyInput.value, 10) || 1;
        v = Math.max(1, v + delta);
        qtyInput.value = v;
    }));

    document.querySelectorAll('.pd-thumb').forEach(t => t.addEventListener('click', () => {
        document.querySelectorAll('.pd-thumb').forEach(x => x.classList.remove('is-active'));
        t.classList.add('is-active');
        if (mainImg && t.dataset.thumb) mainImg.src = t.dataset.thumb;
    }));

    const tabs = document.querySelectorAll('.pd-tab');
    const panels = document.querySelectorAll('.pd-tab__panel');
    tabs.forEach(tab => tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('is-active'));
        panels.forEach(p => p.classList.remove('is-active'));
        tab.classList.add('is-active');
        const panel = document.querySelector(`.pd-tab__panel[data-panel="${tab.dataset.tab}"]`);
        if (panel) panel.classList.add('is-active');
    }));

    // Review list + filter + pagination
    const rvList   = document.querySelector('[data-reviews-list]');
    const rvToggle = document.querySelector('[data-reviews-toggle]');
    const rvCount  = document.querySelector('[data-reviews-count]');
    const rvTabs   = document.querySelectorAll('.pd-rv-tab');
    const rvItems  = rvList ? Array.from(rvList.querySelectorAll('.pd-review')) : [];
    const PREVIEW  = rvList ? parseInt(rvList.dataset.preview, 10) || 5 : 5;
    const STEP     = 10;
    let shown  = PREVIEW;
    let filter = 'all';

    function apply() {
        const filtered = rvItems.filter(it => {
            if (filter === 'all' || filter === 'image') return true;
            return it.dataset.stars === filter;
        });

        // Clamp shown to filtered length
        const visibleCount = Math.min(shown, filtered.length);

        rvItems.forEach(it => it.style.display = 'none');
        filtered.slice(0, visibleCount).forEach(it => it.style.display = '');

        if (rvCount) {
            rvCount.textContent = `Đang hiển thị ${visibleCount} / ${filtered.length} đánh giá`;
        }

        if (rvToggle) {
            const canShowMore = visibleCount < filtered.length;
            const canCollapse = visibleCount > PREVIEW;
            const labelEl = rvToggle.querySelector('[data-btn-label]');
            if (!canShowMore && !canCollapse) {
                rvToggle.style.display = 'none';
            } else {
                rvToggle.style.display = '';
                if (canShowMore) {
                    const remaining = filtered.length - visibleCount;
                    const nextBatch = Math.min(STEP, remaining);
                    if (labelEl) labelEl.textContent = `Xem thêm ${nextBatch} đánh giá`;
                    rvToggle.dataset.action = 'more';
                    rvToggle.classList.remove('is-expanded');
                } else {
                    if (labelEl) labelEl.textContent = 'Thu gọn';
                    rvToggle.dataset.action = 'less';
                    rvToggle.classList.add('is-expanded');
                }
            }
        }
    }

    rvTabs.forEach(t => t.addEventListener('click', () => {
        rvTabs.forEach(x => x.classList.remove('is-active'));
        t.classList.add('is-active');
        filter = t.dataset.rvFilter;
        shown = PREVIEW;
        apply();
    }));

    if (rvToggle) {
        rvToggle.addEventListener('click', () => {
            if (rvToggle.dataset.action === 'less') {
                shown = PREVIEW;
                apply();
                if (rvList) rvList.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                shown += STEP;
                apply();
            }
        });
    }

    apply();
})();

// --- Sync variant/size/qty vào form "Thêm vào giỏ" trước khi submit ---
(() => {
    const form = document.querySelector('[data-cart-form]');
    if (!form) return;

    const variantInput = form.querySelector('[data-cart-variant]');
    const sizeInput    = form.querySelector('[data-cart-size]');
    const qtyInput     = form.querySelector('[data-cart-qty]');

    form.addEventListener('submit', () => {
        const variantEl = document.querySelector('.pd-swatch.is-selected .pd-swatch__label');
        const sizeEl    = document.querySelector('.pd-size.is-selected');
        const qtyEl     = document.getElementById('pdQtyInput');

        if (variantInput) variantInput.value = variantEl?.textContent?.trim() || variantInput.value;
        if (sizeInput)    sizeInput.value    = sizeEl?.textContent?.trim()    || '';
        if (qtyInput) {
            const q = parseInt(qtyEl?.value || '1', 10);
            qtyInput.value = (isNaN(q) || q < 1) ? 1 : Math.min(q, 99);
        }
    });
})();
</script>
@endsection
