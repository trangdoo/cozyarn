<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cozy Yarn - Home</title>
    @vite(['resources/css/home.css', 'resources/js/home.js'])
</head>
<body>
    <main class="home-page">
        @include('partials.site-header', ['isHome' => true])

        <section class="home-carousel" id="home">
            <div class="carousel-viewport">
                <button class="carousel-nav prev" aria-label="Previous slide">‹</button>

                <div class="carousel-track" data-carousel>
                    <article class="slide-card">
                        <div class="slide-bg tone-1">
                            <img src="/images/1.jpg" alt="Handmade Yarn" class="slide-img">
                        </div>
                        <div class="card-content">
                            <small>Welcome to</small>
                            <h2>Handmade Yarn Products</h2>
                            <p>Discover beautiful handmade yarn and accessories for your every project.</p>
                            <a class="btn-main" href="{{ route('shop.index') }}">SHOP NOW</a>
                        </div>
                    </article>

                    <article class="slide-card">
                        <div class="slide-bg tone-2">
                            <img src="/images/2.jpg" alt="Pastel Fibers" class="slide-img">
                        </div>
                        <div class="card-content">
                            <small>Cozy collection</small>
                            <h2>Soft Pastel Fibers</h2>
                            <p>Pick premium yarn bundles to bring comfort and warmth to your creations.</p>
                            <a class="btn-main" href="{{ route('shop.index') }}">SHOP NOW</a>
                        </div>
                    </article>

                    <article class="slide-card">
                        <div class="slide-bg tone-3">
                            <img src="/images/3.jpg" alt="Needles & Tools" class="slide-img">
                        </div>
                        <div class="card-content">
                            <small>Maker essentials</small>
                            <h2>Needles & Cute Tools</h2>
                            <p>Everything you need from starter kits to pro tools in one sweet place.</p>
                            <a class="btn-main" href="{{ route('shop.index') }}">SHOP NOW</a>
                        </div>
                    </article>

                    <article class="slide-card">
                        <div class="slide-bg tone-4">
                            <img src="/images/4.jpg" alt="Starter Packs" class="slide-img">
                        </div>
                        <div class="card-content">
                            <small>Try new patterns</small>
                            <h2>Creative Starter Packs</h2>
                            <p>Follow ready templates and create handmade gifts for friends and family.</p>
                            <a class="btn-main" href="{{ route('shop.index') }}">SHOP NOW</a>
                        </div>
                    </article>
                </div>

                <button class="carousel-nav next" aria-label="Next slide">›</button>
            </div>

            <div class="carousel-dots" data-dots></div>
        </section>

        <div class="tagline-wrap" data-tagline>
            <div class="tagline-glow"></div>

            <div class="tagline-deco tagline-deco--left" aria-hidden="true">
                <svg class="yarn-icon" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="26" stroke="currentColor" stroke-width="2.5"/>
                    <path d="M10 24 Q32 10 54 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M7 36 Q32 20 57 36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 46 Q32 32 54 46" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="32" cy="32" r="5" fill="currentColor" opacity="0.25"/>
                </svg>
            </div>

            <div class="tagline-center">
                <div class="tagline-ornament" aria-hidden="true">
                    <span class="ornament-line"></span>
                    <svg class="ornament-diamond" viewBox="0 0 20 20"><path d="M10 2 L18 10 L10 18 L2 10 Z" fill="currentColor"/></svg>
                    <span class="ornament-line"></span>
                </div>
                <p class="tagline-sub">Chào mừng bạn đến với CozyYarn</p>
                <p class="shop-tagline">
                    Tiệm len handmade nhỏ xinh - nơi mỗi cuộn len<br>
                    được chọn lọc kỹ càng để mang đến sự ấm áp<br>
                    và cảm hứng sáng tạo cho bạn.
                </p>
            </div>

            <div class="tagline-deco tagline-deco--right" aria-hidden="true">
                <svg class="yarn-icon" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="26" stroke="currentColor" stroke-width="2.5"/>
                    <path d="M10 24 Q32 10 54 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M7 36 Q32 20 57 36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 46 Q32 32 54 46" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="32" cy="32" r="5" fill="currentColor" opacity="0.25"/>
                </svg>
            </div>

            <span class="sparkle sparkle--1" aria-hidden="true"></span>
            <span class="sparkle sparkle--2" aria-hidden="true"></span>
            <span class="sparkle sparkle--3" aria-hidden="true"></span>
        </div>

        <section class="bestseller" data-bestseller>
            <div class="bestseller__inner">
                <div class="bestseller__header">
                    <div class="bs-tabs" role="tablist">
                        <button class="bs-tab active" data-tab="all" role="tab">Tất cả</button>
                        <button class="bs-tab" data-tab="yarn" role="tab">Len sợi</button>
                        <button class="bs-tab" data-tab="tools" role="tab">Kim & móc</button>
                        <button class="bs-tab" data-tab="kits" role="tab">Starter Kit</button>
                    </div>
                    <span class="bs-badge">Sản phẩm bán chạy</span>
                </div>

                <div class="bs-slider-wrap">
                <button class="bs-nav bs-nav--prev" aria-label="Trang trước">‹</button>
                <div class="bs-grid" data-bs-grid>
                    @foreach(($featured ?? []) as $p)
                    <article class="bs-card" data-category="{{ $p['data_cat'] }}">
                        <div class="bs-card__img">
                            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}">
                            @if(!empty($p['badge']))
                                <span class="bs-card__tag">{{ $p['badge'] }}</span>
                            @endif
                            <span class="bs-card__img-shine" aria-hidden="true"></span>
                        </div>
                        <div class="bs-card__body">
                            <h3 class="bs-card__name">{{ $p['name'] }}</h3>
                            <p class="bs-card__desc">{{ $p['shortDesc'] }}</p>
                            <div class="bs-card__footer">
                                <span class="bs-card__price">{{ number_format($p['price'], 0, ',', '.') }} ₫</span>
                                <a href="{{ route('shop.product', ['category' => $p['category_slug'], 'product' => $p['slug']]) }}" class="bs-card__btn">Xem ngay</a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                <button class="bs-nav bs-nav--next" aria-label="Trang tiếp">›</button>
                </div>

                <div class="bs-pagination" data-bs-pages></div>
            </div>
        </section>

        {{-- ABOUT US SECTION --}}
        <section class="au-section" id="about" data-reveal-about>
            <div class="au-container">

                <div class="au-left">
                    <div class="au-left__body">
                        <div class="au-left__top">
                            <div class="au-left__text">
                                <span class="au-label">Về chúng tôi</span>
                                <h2 class="au-title">Tiệm len<br>nhỏ xinh<br>của bạn.</h2>
                            </div>
                            <div class="au-left__img-wrap">
                                <img class="au-img au-img--front" src="/images/aboutus1.jpg" alt="CozyYarn">
                                <img class="au-img au-img--back" src="/images/aboutus2.jpg" alt="CozyYarn">
                            </div>
                        </div>
                        <p class="au-intro">CozyYarn được tạo ra từ tình yêu với những sợi len mềm mại và niềm đam mê handmade — nơi mỗi cuộn len đều mang theo một câu chuyện riêng.</p>
                    </div>

                    <div class="au-founder">
                        <a class="au-founder__avatar" href="https://www.instagram.com/_t.298/" target="_blank" rel="noopener" aria-label="Instagram của Chan Doo">
                            <img class="au-founder__photo" src="/images/trangiu.jpg" alt="Chan Doo">
                            <span class="au-founder__ig" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/>
                                </svg>
                            </span>
                        </a>
                        <div class="au-founder__info">
                            <span class="au-founder__name">Chan Doo</span>
                            <span class="au-founder__role">Founder &amp; Creative Director</span>
                            <p class="au-founder__quote">"Mỗi cuộn len là một câu chuyện - tôi ở đây để giúp bạn kể câu chuyện của mình."</p>
                        </div>
                    </div>
                </div>

                <div class="au-right">
                    <div class="au-right__body">
                        <span class="au-label">Câu chuyện</span>
                        <h3 class="au-right-title">Được tạo ra từ<br>tình yêu &amp; sợi len</h3>
                        <p class="au-right-text">CozyYarn bắt đầu từ một góc nhỏ trong căn phòng của một cô gái yêu thích đan móc. Từ những cuộn len đầu tiên được chọn lọc kỹ càng, chúng tôi dần trở thành ngôi nhà chung của cộng đồng yêu handmade.</p>
                        <p class="au-right-text">Dù bạn là người mới hay thợ lâu năm - CozyYarn luôn có thứ gì đó dành riêng cho bạn.</p>
                        <ul class="au-values">
                            <li>
                                <svg viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="8" fill="currentColor" opacity="0.12"/><path d="M5.5 9 L7.5 11 L12.5 6.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Len &amp; phụ kiện chọn lọc kỹ càng
                            </li>
                            <li>
                                <svg viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="8" fill="currentColor" opacity="0.12"/><path d="M5.5 9 L7.5 11 L12.5 6.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Giao hàng toàn quốc 2–4 ngày làm việc
                            </li>
                            <li>
                                <svg viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="8" fill="currentColor" opacity="0.12"/><path d="M5.5 9 L7.5 11 L12.5 6.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Hỗ trợ tư vấn nhiệt tình 24/7
                            </li>
                        </ul>
                    </div>
                    <div class="au-cta">
                        <p class="au-cta__text">Nhận thông báo sản phẩm mới &amp; ưu đãi độc quyền</p>
                        <div class="au-cta__form" data-newsletter>
                            <input type="email" class="au-cta__input" placeholder="Email của bạn..." data-newsletter-input>
                            <button type="button" class="au-cta__btn" data-newsletter-btn
                                onclick="(function(b){var i=b.parentNode.querySelector('[data-newsletter-input]');var v=(i.value||'').trim();if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)){i.focus();i.style.outline='2px solid #d63384';return;}i.disabled=true;b.disabled=true;b.textContent='Đã đăng ký ✓';})(this)">Đăng ký</button>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- STATS BAR --}}
        <section class="stats-bar" data-stats-bar>
            <div class="stats-bar__inner">
                <div class="stats-bar__item">
                    <div class="stats-bar__icon">
                        <svg viewBox="0 0 36 36" fill="none">
                            <rect x="4" y="20" width="7" height="12" rx="2" fill="currentColor" opacity="0.3"/>
                            <rect x="14" y="13" width="7" height="19" rx="2" fill="currentColor" opacity="0.6"/>
                            <rect x="24" y="6" width="7" height="26" rx="2" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="stats-bar__num" data-target="200" data-suffix="+">0</span>
                    <span class="stats-bar__lbl">Loại sản phẩm</span>
                    <span class="stats-bar__underline"></span>
                </div>
                <div class="stats-bar__divider" aria-hidden="true"></div>
                <div class="stats-bar__item">
                    <div class="stats-bar__icon">
                        <svg viewBox="0 0 36 36" fill="none">
                            <circle cx="13" cy="13" r="6" fill="currentColor" opacity="0.35"/>
                            <circle cx="24" cy="11" r="5" fill="currentColor" opacity="0.6"/>
                            <path d="M3 32 C3 24 8 20 13 20 C18 20 23 24 23 32" fill="currentColor" opacity="0.35"/>
                            <path d="M20 22 C23 20 33 22 33 32" fill="currentColor" opacity="0.55"/>
                        </svg>
                    </div>
                    <span class="stats-bar__num" data-target="1500" data-suffix="+">0</span>
                    <span class="stats-bar__lbl">Khách hàng hài lòng</span>
                    <span class="stats-bar__underline"></span>
                </div>
                <div class="stats-bar__divider" aria-hidden="true"></div>
                <div class="stats-bar__item">
                    <div class="stats-bar__icon">
                        <svg viewBox="0 0 36 36" fill="none">
                            <circle cx="18" cy="18" r="13" stroke="currentColor" stroke-width="2.5" opacity="0.3"/>
                            <path d="M11 18 L16 23 L25 13" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="stats-bar__num" data-target="100" data-suffix="%">0</span>
                    <span class="stats-bar__lbl">Chất lượng kiểm định</span>
                    <span class="stats-bar__underline"></span>
                </div>
                <div class="stats-bar__divider" aria-hidden="true"></div>
                <div class="stats-bar__item">
                    <div class="stats-bar__icon">
                        <svg viewBox="0 0 36 36" fill="none">
                            <path d="M18 4 L21.5 13 H31 L24 19 L27 28 L18 22 L9 28 L12 19 L5 13 H14.5Z" fill="currentColor" opacity="0.4"/>
                        </svg>
                    </div>
                    <span class="stats-bar__num" data-target="4.9" data-suffix="★" data-decimal="1">0</span>
                    <span class="stats-bar__lbl">Đánh giá trung bình</span>
                    <span class="stats-bar__underline"></span>
                </div>
            </div>
        </section>

        {{-- VALUES --}}
        <section class="values-section" data-reveal-values>

            {{-- wave divider --}}
            <div class="values-wave" aria-hidden="true">
                <svg viewBox="0 0 1440 64" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,28 C180,64 360,0 540,32 C720,64 900,4 1080,36 C1260,64 1380,20 1440,32 L1440,0 L0,0 Z" fill="white"/>
                </svg>
            </div>

            {{-- decorative elements --}}
            <div class="values-deco values-deco--left" aria-hidden="true">
                <svg viewBox="0 0 130 130" fill="none">
                    <circle cx="65" cy="65" r="55" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="65" cy="65" r="35" stroke="currentColor" stroke-width="1" stroke-dasharray="4 6"/>
                    <circle cx="65" cy="65" r="10" fill="currentColor" opacity="0.2"/>
                </svg>
            </div>
            <div class="values-deco values-deco--right" aria-hidden="true">
                <svg viewBox="0 0 100 100" fill="none">
                    <circle cx="50" cy="50" r="38" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M18 40 Q50 22 82 40" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M14 54 Q50 34 86 54" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M18 66 Q50 50 82 66" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="7" fill="currentColor" opacity="0.2"/>
                </svg>
            </div>

            <div class="values-section__inner">
                <div class="values-section__head">
                    <span class="section-chip">Giá trị cốt lõi</span>
                    <h2 class="section-heading">Chúng tôi mang lại<br>những giá trị gì?</h2>
                    <p class="section-sub">Mỗi quyết định tại CozyYarn đều xuất phát từ một câu hỏi: điều này có tốt hơn cho khách hàng không?</p>
                </div>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-card__icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.1"/>
                                <path d="M24 12 C18 12 13 17 13 23 C13 31 24 38 24 38 C24 38 35 31 35 23 C35 17 30 12 24 12Z" stroke="currentColor" stroke-width="2" fill="currentColor" opacity="0.18"/>
                                <circle cx="24" cy="22" r="4" fill="currentColor" opacity="0.5"/>
                            </svg>
                        </div>
                        <h3>Chọn lọc kỹ càng</h3>
                        <p>Mỗi cuộn len và phụ kiện được kiểm tra chất lượng nghiêm ngặt trước khi đến tay bạn. Chúng tôi không bán những gì chúng tôi không tự hào.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-card__icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.1"/>
                                <rect x="14" y="20" width="20" height="14" rx="3" stroke="currentColor" stroke-width="2"/>
                                <path d="M18 20V16a6 6 0 1 1 12 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="24" cy="27" r="2" fill="currentColor" opacity="0.5"/>
                            </svg>
                        </div>
                        <h3>Giá cả minh bạch</h3>
                        <p>Không phí ẩn, không markup vô lý. Chất lượng tốt với mức giá hợp lý cho mọi người - từ người mới bắt đầu đến thợ thủ công lâu năm.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-card__icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.1"/>
                                <path d="M14 24 L20 30 L34 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Giao hàng tận tâm</h3>
                        <p>Đóng gói cẩn thận từng đơn hàng như gói quà cho người thân. Giao toàn quốc trong 2–4 ngày làm việc với phí ưu đãi.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-card__icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.1"/>
                                <path d="M16 32 Q24 16 32 32" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="24" cy="18" r="4" fill="currentColor" opacity="0.4"/>
                            </svg>
                        </div>
                        <h3>Cộng đồng ấm áp</h3>
                        <p>Không chỉ bán hàng - chúng tôi xây dựng cộng đồng handmade. Chia sẻ mẫu, hỏi kỹ thuật, tìm bạn cùng đam mê.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- REVIEWS --}}
        <section class="reviews-section" id="reviews" data-reveal-reviews>

            {{-- Horizontal corner waves --}}
            <svg class="rv-bg-svg" viewBox="0 0 1440 520" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                {{-- top-left wave blob --}}
                <path d="M0,0 C80,0 160,30 200,90 C240,150 210,220 260,270 C310,320 390,300 420,360 C450,420 400,480 340,500 C280,520 180,500 100,460 C20,420 -20,340 -10,260 C0,180 -30,100 0,0 Z"
                      fill="rgba(255,255,255,0.30)"/>
                <path d="M0,0 C60,0 130,20 165,75 C200,130 170,200 215,248 C260,296 335,278 362,335 C389,392 342,448 285,468 C228,488 136,470 62,433 C-12,396 -45,320 -36,243 C-27,166 -40,83 0,0 Z"
                      fill="rgba(240,150,185,0.18)"/>

                {{-- bottom-right wave blob --}}
                <path d="M1440,520 C1360,520 1280,490 1240,430 C1200,370 1230,300 1180,250 C1130,200 1050,220 1020,160 C990,100 1040,40 1100,20 C1160,0 1260,20 1340,60 C1420,100 1460,180 1450,260 C1440,340 1470,420 1440,520 Z"
                      fill="rgba(255,255,255,0.30)"/>
                <path d="M1440,520 C1365,520 1290,492 1252,434 C1214,376 1243,308 1195,260 C1147,212 1068,231 1040,172 C1012,113 1060,52 1118,30 C1176,8 1272,27 1350,66 C1428,105 1466,184 1456,263 C1446,342 1474,421 1440,520 Z"
                      fill="rgba(240,150,185,0.18)"/>

                {{-- top-right small accent --}}
                <ellipse cx="1360" cy="55" rx="110" ry="70" fill="rgba(255,255,255,0.18)" transform="rotate(-18 1360 55)"/>

                {{-- bottom-left small accent --}}
                <ellipse cx="80" cy="465" rx="100" ry="62" fill="rgba(255,255,255,0.18)" transform="rotate(12 80 465)"/>
            </svg>

            {{-- Sparkle stars --}}
            <svg class="rv-sparkles" viewBox="0 0 1440 520" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M130 80 L134 69 L138 80 L149 84 L138 88 L134 99 L130 88 L119 84 Z" fill="rgba(255,255,255,0.80)"/>
                <path d="M1300 420 L1303 412 L1306 420 L1314 423 L1306 426 L1303 434 L1300 426 L1292 423 Z" fill="rgba(255,255,255,0.78)"/>
                <path d="M1380 100 L1382 95 L1384 100 L1389 102 L1384 104 L1382 109 L1380 104 L1375 102 Z" fill="rgba(255,255,255,0.65)"/>
                <path d="M58 390 L60 385 L62 390 L67 392 L62 394 L60 399 L58 394 L53 392 Z" fill="rgba(255,255,255,0.65)"/>
                <path d="M720 30 L722 25 L724 30 L729 32 L724 34 L722 39 L720 34 L715 32 Z" fill="rgba(255,255,255,0.50)"/>
            </svg>

            {{-- Top wave --}}
            <svg class="rv-wave rv-wave--top" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,30 C240,60 480,0 720,30 C960,60 1200,0 1440,30 L1440,0 L0,0 Z" fill="white"/>
            </svg>

            <div class="reviews-inner">

                {{-- Left info --}}
                <div class="reviews-left">
                    {{-- Big decorative quote --}}
                    <div class="rv-big-quote" aria-hidden="true">"</div>

                    <span class="section-chip">Đánh giá</span>
                    <h2 class="reviews-heading">Khách hàng<br>nói gì về chúng tôi</h2>
                    <p class="reviews-sub">Mỗi cuộn len là một tình yêu - hàng nghìn bạn đã tin tưởng CozyYarn cho từng dự án thủ công của mình.</p>

                    {{-- Rating summary badge --}}
                    <div class="rv-rating-badge">
                        <div class="rv-rating-badge__score">4.9</div>
                        <div class="rv-rating-badge__right">
                            <div class="rv-rating-badge__stars">★★★★★</div>
                            <div class="rv-rating-badge__count">Từ 500+ đánh giá</div>
                        </div>
                    </div>

                    {{-- Mini trust pills --}}
                    <div class="rv-trust-pills">
                        <span class="rv-pill">✓ Hàng chất lượng</span>
                        <span class="rv-pill">✓ Đổi trả dễ dàng</span>
                    </div>
                </div>

                {{-- Right: vertical auto-scroll marquee --}}
                <div class="rv-marquee">
                    <div class="rv-marquee__track">

                        @php
                        $reviews = [
                            ['name'=>'Nguyễn Minh Anh',   'stars'=>5, 'img'=>'review1', 'initials'=>'Minh+Anh',
                             'text'=>'Len mềm mịn, màu sắc chuẩn y ảnh. Đóng gói kỹ càng, giao hàng nhanh. Mình đã mua lần 3 rồi và chắc chắn sẽ còn quay lại!'],
                            ['name'=>'Trần Thị Bảo Châu',  'stars'=>5, 'img'=>'review2', 'initials'=>'Bao+Chau',
                             'text'=>'Shop rất nhiệt tình tư vấn. Mình mới học móc và được hỗ trợ chọn loại len phù hợp cho người mới. Sẽ ủng hộ lâu dài!'],
                            ['name'=>'Lê Thanh Hương',     'stars'=>4, 'img'=>'review3', 'initials'=>'Thanh+Huong',
                             'text'=>'Len đẹp, chất lượng tốt. Giao hàng hơi chậm 1 ngày nhưng nhìn chung vẫn hài lòng. Màu pastel rất dễ phối.'],
                            ['name'=>'Phạm Ngọc Linh',     'stars'=>5, 'img'=>'review4', 'initials'=>'Ngoc+Linh',
                             'text'=>'Đặt combo len làm thú bông, được tặng kèm kim móc xinh xắn. Chất len không bị xù sau khi hoàn thiện sản phẩm. Quá yêu shop!'],
                            ['name'=>'Hoàng Khánh Vân',    'stars'=>5, 'img'=>'review5', 'initials'=>'Khanh+Van',
                             'text'=>'Bao bì quá xinh, nhìn như quà tặng vậy. Len mịn, không bị rối. Mình mua để làm quà sinh nhật bạn, ai cũng khen!'],
                            ['name'=>'Đinh Thùy Liên',     'stars'=>5, 'img'=>'review6', 'initials'=>'Thuy+Lien',
                             'text'=>'Màu len đẹp hơn mình tưởng, tone màu kem và hồng nude rất tinh tế. Sợi len đều tay, móc ra sản phẩm rất mịn. Chắc chắn mua thêm!'],
                            ['name'=>'Ngô Bảo Trân',       'stars'=>5, 'img'=>'review7', 'initials'=>'Bao+Tran',
                             'text'=>'Lần đầu mua len online mà cảm thấy rất yên tâm. Mô tả sản phẩm chính xác, phản hồi tin nhắn nhanh, len giao đúng màu.'],
                            ['name'=>'Vũ Minh Châu',       'stars'=>4, 'img'=>'review8', 'initials'=>'Minh+Chau',
                             'text'=>'Chất len ổn, giá hợp lý. Mình dùng để móc túi tote mini, kết quả rất ưng. Sẽ thử thêm các màu khác lần sau.'],
                            ['name'=>'Phan Thu Hà',        'stars'=>5, 'img'=>'review9', 'initials'=>'Thu+Ha',
                             'text'=>'Shop có nhiều màu len để chọn, team màu pastel của shop đẹp lắm. Mình mua 6 cuộn một lúc mà vẫn thấy thiếu haha!'],
                        ];
                        @endphp

                        {{-- Render 2 lần để loop liền mạch --}}
                        @foreach([1,2] as $pass)
                        @foreach($reviews as $r)
                        <div class="rv-card">
                            <div class="rv-card__top">
                                <img src="/images/{{ $r['img'] }}.jpg" alt="{{ $r['name'] }}" class="rv-avatar"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ $r['initials'] }}&background=fde8f2&color=c0608a&size=56'">
                                <div class="rv-card__info">
                                    <span class="rv-name">{{ $r['name'] }}</span>
                                    <div class="rv-stars">
                                        @for($s=1;$s<=5;$s++){{ $s<=$r['stars'] ? '★' : '☆' }}@endfor
                                    </div>
                                </div>
                                <svg class="rv-quote" viewBox="0 0 40 30" fill="none" aria-hidden="true">
                                    <path d="M2 18C2 10.3 7.4 4.2 16 2l2 3.5C11.5 7.5 9 11 9 14h6v14H2V18zm22 0C24 10.3 29.4 4.2 38 2l2 3.5C33.5 7.5 31 11 31 14h6v14H24V18z" fill="currentColor" opacity="0.12"/>
                                </svg>
                            </div>
                            <p class="rv-text">{{ $r['text'] }}</p>
                        </div>
                        @endforeach
                        @endforeach

                    </div>
                </div>

            </div>

            {{-- Bottom wave --}}
            <svg class="rv-wave rv-wave--bot" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,30 C240,0 480,60 720,30 C960,0 1200,60 1440,30 L1440,60 L0,60 Z" fill="white"/>
            </svg>

        </section>

        {{-- BLOG PREVIEW --}}
        <section class="blog-preview" data-reveal-blog>
            <div class="blog-preview__inner">
                <div class="blog-preview__head">
                    <span class="section-chip">Tin tức</span>
                    <h2 class="section-heading">Tin tức &amp; bài viết mới nhất</h2>
                    <p class="section-sub">Cập nhật những câu chuyện, xu hướng và mẹo vặt mới nhất về thế giới len sợi handmade — đồng hành cùng bạn trong từng dự án sáng tạo.</p>
                </div>

                @php
                $posts = [
                    [
                        'img'   => '1.jpg',
                        'day'   => '14',
                        'month' => 'Th7',
                        'tag'   => 'Tin tức',
                        'title' => 'Cách chọn len phù hợp cho người mới bắt đầu',
                        'desc'  => 'Khi mới học đan móc, việc chọn đúng loại len sẽ giúp bạn dễ thao tác và có thành phẩm đẹp ngay từ dự án đầu tiên.',
                    ],
                    [
                        'img'   => '2.jpg',
                        'day'   => '08',
                        'month' => 'Th7',
                        'tag'   => 'Tin tức',
                        'title' => 'Xu hướng màu pastel cho mùa handmade 2025',
                        'desc'  => 'Trong bối cảnh handmade ngày càng được ưa chuộng, tone pastel nhẹ nhàng tiếp tục là lựa chọn được yêu thích nhất.',
                    ],
                    [
                        'img'   => '3.jpg',
                        'day'   => '08',
                        'month' => 'Th7',
                        'tag'   => 'Tin tức',
                        'title' => 'Mẹo bảo quản len để luôn mềm mại như mới',
                        'desc'  => 'Bảo quản len đúng cách giúp giữ được độ mịn, màu sắc và độ bền — đặc biệt với những cuộn len handmade cao cấp.',
                    ],
                ];
                @endphp

                <div class="blog-preview__grid">
                    @foreach($posts as $p)
                    <article class="bp-card">
                        <a class="bp-card__link" href="/blog">
                            <div class="bp-card__img">
                                <img src="/images/{{ $p['img'] }}" alt="{{ $p['title'] }}">
                                <span class="bp-card__date" aria-hidden="true">
                                    <span class="bp-card__day">{{ $p['day'] }}</span>
                                    <span class="bp-card__month">{{ $p['month'] }}</span>
                                </span>
                            </div>
                            <div class="bp-card__body">
                                <span class="bp-card__tag">{{ $p['tag'] }}</span>
                                <h3 class="bp-card__title">{{ $p['title'] }}</h3>
                                <p class="bp-card__excerpt">{{ $p['desc'] }}</p>
                                <span class="bp-card__more">
                                    Chi Tiết
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M7 17L17 7M17 7H9M17 7V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </a>
                    </article>
                    @endforeach
                </div>

                <div class="blog-preview__footer">
                    <a href="/blog" class="bp-view-all">Xem tất cả bài viết</a>
                </div>
            </div>
        </section>

        {{-- CONTACT --}}
        <section class="contact-section" id="contact" data-reveal-contact>

            {{-- Hero banner --}}
            <div class="contact__hero">
                <svg class="ch-chevron ch-chevron--left" viewBox="0 0 90 50" fill="none" aria-hidden="true">
                    <path d="M5 5 L22 25 L5 45" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M25 5 L42 25 L25 45" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M45 5 L62 25 L45 45" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg class="ch-chevron ch-chevron--right" viewBox="0 0 90 50" fill="none" aria-hidden="true">
                    <path d="M5 5 L22 25 L5 45" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M25 5 L42 25 L25 45" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M45 5 L62 25 L45 45" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="section-chip">Liên hệ</span>
                <h2 class="contact__title">Liên hệ với chúng tôi</h2>
                <svg class="contact__wave-line" viewBox="0 0 200 18" fill="none" aria-hidden="true">
                    <path d="M0,9 C25,1 50,17 75,9 C100,1 125,17 150,9 C175,1 190,15 200,9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <p class="contact__sub">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn trong từng dự án.</p>

                {{-- Social icons scattered --}}
                <div class="hero-social" aria-label="Mạng xã hội">
                    <a href="https://www.facebook.com/ne.mituot.257" class="hsc hsc--fb" target="_blank" rel="noopener" title="Facebook">
                        <svg class="sc-bow" viewBox="0 0 40 24" fill="none" aria-hidden="true"><path d="M20 12C16 8 7 3 5 7C3 11 11 12 20 12Z" fill="currentColor"/><path d="M20 12C16 16 7 21 5 17C3 13 11 12 20 12Z" fill="currentColor" opacity="0.7"/><path d="M20 12C24 8 33 3 35 7C37 11 29 12 20 12Z" fill="currentColor"/><path d="M20 12C24 16 33 21 35 17C37 13 29 12 20 12Z" fill="currentColor" opacity="0.7"/><ellipse cx="20" cy="12" rx="4" ry="3" fill="currentColor"/></svg>
                        <svg class="hsc-logo" viewBox="0 0 24 24"><path d="M17 4h-3a5 5 0 0 0-5 5v3H6v4h3v8h4v-8h3l1-4h-4V9a1 1 0 0 1 1-1h3V4z" fill="white"/></svg>
                    </a>
                    <a href="https://www.facebook.com/messages/e2ee/t/7308852142511151" class="hsc hsc--ms" target="_blank" rel="noopener" title="Messenger">
                        <svg class="sc-bow" viewBox="0 0 40 24" fill="none" aria-hidden="true"><path d="M20 12C16 8 7 3 5 7C3 11 11 12 20 12Z" fill="currentColor"/><path d="M20 12C16 16 7 21 5 17C3 13 11 12 20 12Z" fill="currentColor" opacity="0.7"/><path d="M20 12C24 8 33 3 35 7C37 11 29 12 20 12Z" fill="currentColor"/><path d="M20 12C24 16 33 21 35 17C37 13 29 12 20 12Z" fill="currentColor" opacity="0.7"/><ellipse cx="20" cy="12" rx="4" ry="3" fill="currentColor"/></svg>
                        <svg class="hsc-logo" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.5 2 2 6.1 2 11.2c0 2.9 1.4 5.4 3.6 7.1v3.5l3.3-1.8c.9.2 1.8.3 2.8.3 5.5 0 10-4.1 10-9.1S17.5 2 12 2zm1 12.3-2.5-2.7-4.9 2.7 5.4-5.7 2.6 2.7 4.8-2.7-5.4 5.7z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@_doo.ttter" class="hsc hsc--tt" target="_blank" rel="noopener" title="TikTok">
                        <svg class="sc-bow" viewBox="0 0 40 24" fill="none" aria-hidden="true"><path d="M20 12C16 8 7 3 5 7C3 11 11 12 20 12Z" fill="currentColor"/><path d="M20 12C16 16 7 21 5 17C3 13 11 12 20 12Z" fill="currentColor" opacity="0.7"/><path d="M20 12C24 8 33 3 35 7C37 11 29 12 20 12Z" fill="currentColor"/><path d="M20 12C24 16 33 21 35 17C37 13 29 12 20 12Z" fill="currentColor" opacity="0.7"/><ellipse cx="20" cy="12" rx="4" ry="3" fill="currentColor"/></svg>
                        <svg class="hsc-logo" viewBox="0 0 24 24" fill="white"><path d="M19.6 8.2a4.8 4.8 0 0 1-4.6-4.2H12v10.5a2.4 2.4 0 1 1-1.7-2.3V9.4A5.4 5.4 0 1 0 15 14.4V9.9a7.7 7.7 0 0 0 4.6 1.5V8.6l-.0-.4z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/_t.298/" class="hsc hsc--ig" target="_blank" rel="noopener" title="Instagram">
                        <svg class="sc-bow" viewBox="0 0 40 24" fill="none" aria-hidden="true"><path d="M20 12C16 8 7 3 5 7C3 11 11 12 20 12Z" fill="currentColor"/><path d="M20 12C16 16 7 21 5 17C3 13 11 12 20 12Z" fill="currentColor" opacity="0.7"/><path d="M20 12C24 8 33 3 35 7C37 11 29 12 20 12Z" fill="currentColor"/><path d="M20 12C24 16 33 21 35 17C37 13 29 12 20 12Z" fill="currentColor" opacity="0.7"/><ellipse cx="20" cy="12" rx="4" ry="3" fill="currentColor"/></svg>
                        <svg class="hsc-logo" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1.2" fill="white" stroke="none"/></svg>
                    </a>
                    <a href="#" class="hsc hsc--zl" target="_blank" rel="noopener" title="Zalo">
                        <svg class="sc-bow" viewBox="0 0 40 24" fill="none" aria-hidden="true"><path d="M20 12C16 8 7 3 5 7C3 11 11 12 20 12Z" fill="currentColor"/><path d="M20 12C16 16 7 21 5 17C3 13 11 12 20 12Z" fill="currentColor" opacity="0.7"/><path d="M20 12C24 8 33 3 35 7C37 11 29 12 20 12Z" fill="currentColor"/><path d="M20 12C24 16 33 21 35 17C37 13 29 12 20 12Z" fill="currentColor" opacity="0.7"/><ellipse cx="20" cy="12" rx="4" ry="3" fill="currentColor"/></svg>
                        <svg class="hsc-logo" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="4" stroke="white" stroke-width="1.8"/><path d="M7 9h5l-5 6h6M13 9l3 3-3 3" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>

            <div class="contact__inner">

                <div class="contact__grid">
                    <form class="contact-form" data-contact-form onsubmit="event.preventDefault();this.dataset.sent='1';this.querySelector('.cf-submit').textContent='Đã gửi ✓ Cảm ơn bạn';this.querySelectorAll('input,textarea,button').forEach(el=>el.disabled=true);">
                        <div class="cf-row">
                            <input type="email" placeholder="Email" class="cf-input" required>
                            <input type="tel" placeholder="Số điện thoại" class="cf-input" required>
                        </div>
                        <input type="text" placeholder="Họ và tên" class="cf-input cf-input--full" required>
                        <textarea placeholder="Nội dung tin nhắn của bạn..." class="cf-textarea" required></textarea>
                        <button type="submit" class="cf-submit">Gửi tin nhắn</button>
                    </form>

                    <div class="contact-map contact-map--side">
                        <iframe
                            src="https://maps.google.com/maps?q=My+Dinh,+Nam+Tu+Liem,+Ha+Noi,+Vietnam&t=&z=15&ie=UTF8&iwloc=&output=embed"
                            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <div class="contact-info">
                    <div class="ci-card">
                        <div class="ci-icon">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M6.6 10.8a15.4 15.4 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.5 11.5 0 0 0 3.6.6 1 1 0 0 1 1 1V18a1 1 0 0 1-1 1A17 17 0 0 1 3 5a1 1 0 0 1 1-1h2.5a1 1 0 0 1 1 1c0 1.25.2 2.45.6 3.6a1 1 0 0 1-.25 1L6.6 10.8Z" stroke="currentColor" stroke-width="1.8"/></svg>
                        </div>
                        <span class="ci-label">Điện thoại</span>
                        <span class="ci-val">0346543266</span>
                        <p class="ci-desc">Thứ 2 – Thứ 7, 8:00 – 20:00</p>
                    </div>
                    <div class="ci-card">
                        <div class="ci-icon">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M2 8 L12 14 L22 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </div>
                        <span class="ci-label">Email</span>
                        <span class="ci-val">trangsocute2908@gmail.com</span>
                        <p class="ci-desc">Phản hồi trong vòng 24 giờ</p>
                    </div>
                    <div class="ci-card">
                        <div class="ci-icon">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.9-3.1-7-7-7Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                        </div>
                        <span class="ci-label">Địa chỉ</span>
                        <span class="ci-val">Mỹ Đình, Hà Nội</span>
                        <p class="ci-desc">Nam Từ Liêm, Hà Nội, Việt Nam</p>
                    </div>
                </div>

            </div>{{-- /.contact__inner --}}
        </section>

        {{-- FOOTER --}}
        <footer class="site-footer">
            <svg class="ft-wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,40 C240,0 480,70 720,40 C960,10 1200,60 1440,30 L1440,0 L0,0 Z" fill="#fff"/>
            </svg>

            <div class="ft-deco ft-deco--1" aria-hidden="true"></div>
            <div class="ft-deco ft-deco--2" aria-hidden="true"></div>
            <div class="ft-deco ft-deco--3" aria-hidden="true"></div>

            <div class="ft-inner">

                <div class="ft-grid">
                    {{-- Brand --}}
                    <div class="ft-col ft-col--brand">
                        <a href="/" class="ft-brand">
                            <img src="/images/avartar.jpg" alt="CozyYarn" class="ft-brand__logo">
                            <span class="ft-brand__text">
                                <span class="ft-brand__name">CozyYarn</span>
                                <span class="ft-brand__tag">Handmade · Pastel · Cozy</span>
                            </span>
                        </a>
                        <p class="ft-about">Tiệm len handmade nhỏ xinh - nơi mỗi cuộn len được chọn lọc kỹ càng để mang đến sự ấm áp và cảm hứng sáng tạo cho bạn.</p>
                        <ul class="ft-meta">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.9-3.1-7-7-7Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                                Mỹ Đình, Nam Từ Liêm, Hà Nội
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none"><path d="M6.6 10.8a15.4 15.4 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.5 11.5 0 0 0 3.6.6 1 1 0 0 1 1 1V18a1 1 0 0 1-1 1A17 17 0 0 1 3 5a1 1 0 0 1 1-1h2.5a1 1 0 0 1 1 1c0 1.25.2 2.45.6 3.6a1 1 0 0 1-.25 1L6.6 10.8Z" stroke="currentColor" stroke-width="1.8"/></svg>
                                0346543266
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M2 8 L12 14 L22 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                trangsocute2908@gmail.com
                            </li>
                        </ul>
                        <div class="ft-social">
                            <a href="https://www.facebook.com/ne.mituot.257" target="_blank" rel="noopener" aria-label="Facebook" class="ft-soc ft-soc--fb">
                                <svg viewBox="0 0 24 24"><path d="M17 4h-3a5 5 0 0 0-5 5v3H6v4h3v8h4v-8h3l1-4h-4V9a1 1 0 0 1 1-1h3V4z" fill="currentColor"/></svg>
                            </a>
                            <a href="https://www.instagram.com/_t.298/" target="_blank" rel="noopener" aria-label="Instagram" class="ft-soc ft-soc--ig">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
                            </a>
                            <a href="https://www.tiktok.com/@_doo.ttter" target="_blank" rel="noopener" aria-label="TikTok" class="ft-soc ft-soc--tt">
                                <svg viewBox="0 0 24 24"><path d="M19.6 8.2a4.8 4.8 0 0 1-4.6-4.2H12v10.5a2.4 2.4 0 1 1-1.7-2.3V9.4A5.4 5.4 0 1 0 15 14.4V9.9a7.7 7.7 0 0 0 4.6 1.5V8.6z" fill="currentColor"/></svg>
                            </a>
                            <a href="https://www.facebook.com/messages/e2ee/t/7308852142511151" target="_blank" rel="noopener" aria-label="Messenger" class="ft-soc ft-soc--ms">
                                <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.1 2 11.2c0 2.9 1.4 5.4 3.6 7.1v3.5l3.3-1.8c.9.2 1.8.3 2.8.3 5.5 0 10-4.1 10-9.1S17.5 2 12 2zm1 12.3-2.5-2.7-4.9 2.7 5.4-5.7 2.6 2.7 4.8-2.7-5.4 5.7z" fill="currentColor"/></svg>
                            </a>
                            <a href="#" target="_blank" rel="noopener" aria-label="Zalo" class="ft-soc ft-soc--zl">
                                <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="4" stroke="currentColor" stroke-width="1.8"/><path d="M7 9h5l-5 6h6M13 9l3 3-3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div class="ft-col">
                        <h3 class="ft-title">Danh mục</h3>
                        <ul class="ft-links">
                            <li><a href="/shop/len-soi">Len sợi các loại</a></li>
                            <li><a href="/shop/kim-moc">Kim &amp; Móc</a></li>
                            <li><a href="/shop/starter-kit">Starter Kit</a></li>
                            <li><a href="/shop/phu-kien">Phụ kiện handmade</a></li>
                            <li><a href="/shop">Tất cả sản phẩm</a></li>
                            <li><a href="/shop">Sale &amp; Ưu đãi</a></li>
                        </ul>
                    </div>

                    {{-- Policies --}}
                    <div class="ft-col">
                        <h3 class="ft-title">Chính sách</h3>
                        <ul class="ft-links">
                            <li><a href="/chinh-sach/mua-hang">Chính sách mua hàng</a></li>
                            <li><a href="/chinh-sach/doi-tra">Chính sách đổi trả</a></li>
                            <li><a href="/chinh-sach/van-chuyen">Chính sách vận chuyển</a></li>
                            <li><a href="/chinh-sach/bao-mat">Chính sách bảo mật</a></li>
                            <li><a href="/chinh-sach/faq">Câu hỏi thường gặp</a></li>
                            <li><a href="/chinh-sach/huong-dan-dat-hang">Hướng dẫn đặt hàng</a></li>
                        </ul>
                    </div>

                    {{-- Hours + badges --}}
                    <div class="ft-col">
                        <h3 class="ft-title">Giờ làm việc</h3>
                        <ul class="ft-hours">
                            <li><span>Thứ 2 – Thứ 6</span><strong>8:00 – 21:00</strong></li>
                            <li><span>Thứ 7</span><strong>9:00 – 21:00</strong></li>
                            <li><span>Chủ nhật</span><strong>9:00 – 18:00</strong></li>
                        </ul>
                        <div class="ft-badges">
                            <div class="ft-badge">
                                <span class="ft-badge__icon ft-badge__icon--pink">
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l2.5 6.5L21 10l-5 4.5L17.5 22 12 18 6.5 22 8 14.5 3 10l6.5-1.5L12 2z" fill="currentColor"/></svg>
                                </span>
                                <span class="ft-badge__body">
                                    <strong>Chính hãng 100%</strong>
                                    <small>Len chọn lọc kỹ</small>
                                </span>
                            </div>
                            <div class="ft-badge">
                                <span class="ft-badge__icon ft-badge__icon--rose">
                                    <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="7" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M17 11h3l1 3v3h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="17" cy="18" r="2" stroke="currentColor" stroke-width="1.8"/></svg>
                                </span>
                                <span class="ft-badge__body">
                                    <strong>Giao nhanh 2–4 ngày</strong>
                                    <small>Toàn quốc</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ft-bottom">
                    <p class="ft-copy">© 2025 <strong>CozyYarn</strong>. Được làm bằng <span class="ft-heart">♥</span> bởi cộng đồng handmade Việt Nam.</p>
                    <div class="ft-bottom__links">
                        <a href="/chinh-sach/mua-hang">Điều khoản</a>
                        <a href="/chinh-sach/bao-mat">Bảo mật</a>
                        <a href="/chinh-sach/faq">FAQ</a>
                        <a href="/shop">Sitemap</a>
                    </div>
                    <div class="ft-pay" aria-label="Phương thức thanh toán">
                        <span class="ft-pay__label">Thanh toán:</span>
                        <span class="ft-pay__chip">VISA</span>
                        <span class="ft-pay__chip ft-pay__chip--mc">MC</span>
                        <span class="ft-pay__chip ft-pay__chip--momo">MoMo</span>
                        <span class="ft-pay__chip ft-pay__chip--cod">COD</span>
                    </div>
                </div>

            </div>
        </footer>

    </main>

    {{-- BACK TO TOP --}}
    <button type="button" class="back-to-top" data-back-top aria-label="Về đầu trang">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 14l6-6 6 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="back-to-top__ring" aria-hidden="true"></span>
    </button>
</body>
</html>