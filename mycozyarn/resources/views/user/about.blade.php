<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về Chúng Tôi - CozyYarn</title>
    @vite(['resources/css/about.css', 'resources/js/home.js'])
</head>
<body>
    <main class="about-page">

        {{-- Header --}}
        <header class="site-header">
            <div class="site-header__inner">
                <div class="top-nav">
                    <a href="/" class="brand-wrap">
                        <span class="brand-avatar" aria-hidden="true">CY</span>
                        <span class="brand">CozyYarn</span>
                    </a>
                    <nav class="menu">
                        <a href="/">Home</a>
                        <a href="#">Shop</a>
                        <a href="#">Blog</a>
                        <a href="/about" class="active">About us</a>
                        <a href="#">Contact</a>
                    </nav>
                    <div class="header-actions">
                        <a href="#" class="action-pill">
                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/>
                            </svg>
                            <span>Tìm kiếm</span>
                        </a>
                        <a href="#" class="action-pill">
                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/>
                            </svg>
                            <span>Tài khoản</span>
                        </a>
                        <a href="#" class="action-pill">
                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>
                                <path d="M3 4h2l2.2 10.3a1 1 0 0 0 1 .7h9.8a1 1 0 0 0 1-.8L21 8H7"/>
                            </svg>
                            <span>Giỏ hàng</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="about-hero" data-reveal>
            <div class="hero-bg-blob blob-1" aria-hidden="true"></div>
            <div class="hero-bg-blob blob-2" aria-hidden="true"></div>
            <div class="about-hero__inner">
                <span class="hero-label">Về chúng tôi</span>
                <h1 class="hero-title">Câu chuyện của<br><span class="hero-title--accent">CozyYarn</span></h1>
                <p class="hero-subtitle">Một tiệm len nhỏ xinh được tạo ra từ tình yêu với những sợi len mềm mại và niềm đam mê handmade.</p>
                <div class="hero-ornament" aria-hidden="true">
                    <span class="orn-line"></span>
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2 C8 2 4 6 4 10 C4 16 12 22 12 22 C12 22 20 16 20 10 C20 6 16 2 12 2Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="10" r="3" fill="currentColor" opacity="0.3"/></svg>
                    <span class="orn-line"></span>
                </div>
            </div>
        </section>

        {{-- Story --}}
        <section class="about-story" data-reveal>
            <div class="about-story__inner">
                <div class="story-img-wrap">
                    <div class="story-img-card story-img-card--main">
                        <div class="story-img-placeholder">
                            <svg viewBox="0 0 80 80" fill="none">
                                <circle cx="40" cy="40" r="32" stroke="currentColor" stroke-width="2"/>
                                <path d="M16 30 Q40 14 64 30" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M13 44 Q40 26 67 44" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M16 56 Q40 40 64 56" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <circle cx="40" cy="40" r="7" fill="currentColor" opacity="0.2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="story-img-card story-img-card--sub">
                        <div class="story-img-placeholder story-img-placeholder--sm">
                            <svg viewBox="0 0 48 48" fill="none">
                                <path d="M10 38 Q24 10 38 38" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="24" cy="20" r="5" fill="currentColor" opacity="0.25"/>
                            </svg>
                        </div>
                    </div>
                    <span class="story-badge">Est. 2022</span>
                </div>

                <div class="story-text">
                    <span class="section-label">Câu chuyện của chúng tôi</span>
                    <h2 class="story-title">Được tạo ra từ<br>tình yêu &amp; sợi len</h2>
                    <p class="story-body">CozyYarn bắt đầu từ một góc nhỏ trong căn phòng của một cô gái yêu thích đan móc. Từ những cuộn len đầu tiên được chọn lựa kỹ càng, CozyYarn dần trở thành ngôi nhà chung của cộng đồng yêu handmade.</p>
                    <p class="story-body">Chúng tôi tin rằng mỗi sản phẩm thủ công đều mang theo một câu chuyện — và chúng tôi ở đây để giúp bạn kể câu chuyện của mình qua từng mũi kim, từng cuộn len.</p>
                    <a href="#" class="story-btn">Khám phá sản phẩm</a>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section class="about-stats" data-reveal>
            <div class="about-stats__inner">
                <div class="stat-card" data-count="200" data-suffix="+">
                    <div class="stat-icon">
                        <svg viewBox="0 0 40 40" fill="none">
                            <rect x="6" y="18" width="8" height="16" rx="2" fill="currentColor" opacity="0.25"/>
                            <rect x="16" y="12" width="8" height="22" rx="2" fill="currentColor" opacity="0.5"/>
                            <rect x="26" y="6" width="8" height="28" rx="2" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="stat-num">200<span class="stat-suffix">+</span></span>
                    <span class="stat-label">Loại sản phẩm</span>
                </div>

                <div class="stat-divider" aria-hidden="true"></div>

                <div class="stat-card" data-count="1500" data-suffix="+">
                    <div class="stat-icon">
                        <svg viewBox="0 0 40 40" fill="none">
                            <circle cx="15" cy="14" r="6" fill="currentColor" opacity="0.3"/>
                            <circle cx="26" cy="12" r="5" fill="currentColor" opacity="0.5"/>
                            <path d="M4 34 C4 26 10 22 15 22 C20 22 26 26 26 34" fill="currentColor" opacity="0.3"/>
                            <path d="M22 24 C25 22 36 24 36 34" fill="currentColor" opacity="0.5"/>
                        </svg>
                    </div>
                    <span class="stat-num">1.500<span class="stat-suffix">+</span></span>
                    <span class="stat-label">Khách hàng hài lòng</span>
                </div>

                <div class="stat-divider" aria-hidden="true"></div>

                <div class="stat-card" data-count="100" data-suffix="%">
                    <div class="stat-icon">
                        <svg viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="14" stroke="currentColor" stroke-width="2.5" opacity="0.3"/>
                            <path d="M12 20 L17 25 L28 14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="stat-num">100<span class="stat-suffix">%</span></span>
                    <span class="stat-label">Chất lượng kiểm định</span>
                </div>

                <div class="stat-divider" aria-hidden="true"></div>

                <div class="stat-card" data-count="4.9" data-suffix="★">
                    <div class="stat-icon">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M20 5 L23.5 14.5 H34 L26 20.5 L29 30 L20 24 L11 30 L14 20.5 L6 14.5 H16.5Z" fill="currentColor" opacity="0.35"/>
                        </svg>
                    </div>
                    <span class="stat-num">4.9<span class="stat-suffix">★</span></span>
                    <span class="stat-label">Đánh giá trung bình</span>
                </div>
            </div>
        </section>

        {{-- Values --}}
        <section class="about-values" data-reveal>
            <div class="about-values__inner">
                <div class="values-header">
                    <span class="section-label">Tại sao chọn chúng tôi</span>
                    <h2 class="values-title">Điều làm nên CozyYarn</h2>
                </div>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.08"/>
                                <path d="M24 12 C18 12 13 17 13 23 C13 31 24 38 24 38 C24 38 35 31 35 23 C35 17 30 12 24 12Z" stroke="currentColor" stroke-width="2" fill="currentColor" opacity="0.2"/>
                            </svg>
                        </div>
                        <h3 class="value-name">Chọn lọc kỹ càng</h3>
                        <p class="value-desc">Mỗi cuộn len và phụ kiện đều được kiểm tra chất lượng trước khi đến tay bạn.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.08"/>
                                <rect x="14" y="20" width="20" height="14" rx="3" stroke="currentColor" stroke-width="2"/>
                                <path d="M18 20 V16 C18 12.7 20.7 10 24 10 C27.3 10 30 12.7 30 16 V20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3 class="value-name">Giá cả hợp lý</h3>
                        <p class="value-desc">Chất lượng tốt với mức giá phù hợp cho mọi đối tượng, từ người mới đến thợ lâu năm.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.08"/>
                                <path d="M14 24 L20 30 L34 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="value-name">Giao hàng nhanh</h3>
                        <p class="value-desc">Đóng gói cẩn thận, giao hàng toàn quốc trong 2–4 ngày làm việc.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <svg viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="20" fill="currentColor" opacity="0.08"/>
                                <path d="M16 32 Q24 16 32 32" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="24" cy="18" r="4" fill="currentColor" opacity="0.3"/>
                            </svg>
                        </div>
                        <h3 class="value-name">Hỗ trợ nhiệt tình</h3>
                        <p class="value-desc">Đội ngũ luôn sẵn sàng tư vấn len, pattern và hỗ trợ bạn trong từng dự án.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="about-cta" data-reveal>
            <div class="about-cta__inner">
                <span class="cta-blob" aria-hidden="true"></span>
                <h2 class="cta-title">Sẵn sàng bắt đầu hành trình handmade?</h2>
                <p class="cta-sub">Khám phá hơn 200 loại len và phụ kiện đang chờ bạn tại CozyYarn.</p>
                <a href="#" class="cta-btn">Mua sắm ngay</a>
            </div>
        </section>

    </main>
</body>
</html>
