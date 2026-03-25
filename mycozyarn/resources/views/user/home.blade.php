<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CozYarn - Sợi Len Ấm Áp </title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600;700&family=Nunito+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: 'Nunito Sans', sans-serif;
  background: #fff;
  color: #333;
  font-size: 14px;
}

/* ─── TOP ANNOUNCEMENT BAR ─── */
.announcement-bar {
  background: #1a1a1a;
  color: #fff;
  text-align: center;
  padding: 9px 20px;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.01em;
}
.announcement-bar span { color: #2dd4a0; }

/* ─── HEADER ─── */
header {
  background: #fff;
  border-bottom: 1px solid #eee;
  position: sticky; top: 0; z-index: 200;
}
.header-main {
  display: flex; align-items: center;
  padding: 14px 32px; gap: 24px;
  max-width: 1400px; margin: 0 auto;
}
.logo {
  font-family: 'Nunito Sans', sans-serif;
  font-size: 2rem; font-weight: 800;
  color: #2dd4a0;
  text-decoration: none;
  letter-spacing: -0.03em;
  flex-shrink: 0;
  font-style: italic;
}
.search-wrap {
  flex: 1; display: flex;
  border: 1.5px solid #ddd; border-radius: 4px;
  overflow: hidden; max-width: 600px;
}
.search-input {
  flex: 1; padding: 10px 16px;
  border: none; outline: none;
  font-size: 14px; font-family: inherit;
  color: #555;
}
.search-input::placeholder { color: #aaa; }
.search-btn {
  background: #2dd4a0; border: none;
  padding: 0 18px; cursor: pointer;
  display: flex; align-items: center;
}
.search-btn svg { color: #fff; }

.header-right {
  display: flex; align-items: center;
  gap: 24px; margin-left: auto;
  flex-shrink: 0;
}
.header-country {
  font-size: 13px; color: #555; cursor: pointer;
  line-height: 1.3;
}
.header-country strong { display: block; color: #222; font-size: 14px; }
.header-divider { width: 1px; height: 36px; background: #ddd; }
.header-account { font-size: 13px; color: #555; cursor: pointer; text-align: right; line-height: 1.3; }
.header-account strong { display: block; color: #222; }
.cart-btn {
  display: flex; align-items: center; gap: 8px;
  font-size: 15px; color: #222; font-weight: 600;
  cursor: pointer; text-decoration: none;
  position: relative;
}
.cart-icon-wrap { position: relative; }
.cart-count-badge {
  position: absolute; top: -8px; right: -8px;
  background: #2dd4a0; color: #fff;
  font-size: 10px; font-weight: 700;
  width: 18px; height: 18px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

/* ─── NAV ─── */
.main-nav {
  background: #fff;
  border-bottom: 1px solid #eee;
}
.nav-inner {
  display: flex; align-items: center;
  max-width: 1400px; margin: 0 auto;
  padding: 0 32px;
}
.nav-item {
  position: relative;
}
.nav-link {
  display: flex; align-items: center; gap: 4px;
  padding: 13px 16px;
  font-size: 13.5px; font-weight: 600; color: #333;
  text-decoration: none; cursor: pointer;
  white-space: nowrap;
  border-bottom: 2px solid transparent;
  transition: color 0.15s, border-color 0.15s;
}
.nav-link:hover { color: #2dd4a0; border-bottom-color: #2dd4a0; }
.nav-link .arrow {
  font-size: 10px; color: #999; transition: transform 0.2s;
}
.nav-item:hover .arrow { transform: rotate(180deg); }

/* Dropdown */
.nav-dropdown {
  position: absolute; top: 100%; left: 0;
  background: #fff; min-width: 200px;
  border: 1px solid #eee; border-top: 2px solid #2dd4a0;
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  z-index: 300; opacity: 0; visibility: hidden;
  transform: translateY(8px);
  transition: all 0.2s;
}
.nav-item:hover .nav-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
.nav-dropdown a {
  display: block; padding: 10px 18px;
  font-size: 13px; color: #444; text-decoration: none;
  transition: background 0.15s, color 0.15s;
}
.nav-dropdown a:hover { background: #f5fdfb; color: #2dd4a0; }

/* ─── HERO CAROUSEL ─── */
.hero {
  position: relative; overflow: hidden;
  background: #f8f4f6;
  max-width: 1400px; margin: 0 auto;
}
.carousel-track {
  display: flex;
  transition: transform 0.7s cubic-bezier(0.77,0,0.18,1);
}
.slide {
  min-width: 100%; position: relative;
  display: flex; align-items: stretch;
  min-height: 420px;
}
/* Slide layouts: image left, text right */
.slide-img {
  flex: 1.2; overflow: hidden;
  position: relative;
}
.slide-img-inner {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 120px;
}
/* Slide 1 - purple/mauve */
.s1 { background: linear-gradient(135deg, #c8b0d8 0%, #dbbfd8 50%, #e8d0e0 100%); }
.s1 .slide-text-wrap { background: #e8d5dc; }
/* Slide 2 - green */
.s2 { background: linear-gradient(135deg, #98c8a8 0%, #c8dcc0 100%); }
.s2 .slide-text-wrap { background: #e0ede8; }
/* Slide 3 - terracotta */
.s3 { background: linear-gradient(135deg, #e8b898 0%, #f0d0b8 100%); }
.s3 .slide-text-wrap { background: #f5e8e0; }
/* Slide 4 - blue */
.s4 { background: linear-gradient(135deg, #98b8d8 0%, #c0d4e8 100%); }
.s4 .slide-text-wrap { background: #dde8f0; }
/* Slide 5 - yellow */
.s5 { background: linear-gradient(135deg, #e8d898 0%, #f0e8c0 100%); }
.s5 .slide-text-wrap { background: #f5f0e0; }
/* Slide 6 - red */
.s6 { background: linear-gradient(135deg, #e89898 0%, #f0c0b8 100%); }
.s6 .slide-text-wrap { background: #f5e0dc; }

.slide-text-wrap {
  width: 340px; flex-shrink: 0;
  display: flex; flex-direction: column;
  justify-content: center; align-items: flex-start;
  padding: 48px 48px 48px 40px;
}
.slide-script {
  font-family: 'Dancing Script', cursive;
  font-size: 1.5rem; color: #555;
  margin-bottom: 6px; font-weight: 400;
}
.slide-main {
  font-size: 2.2rem; font-weight: 800;
  color: #1a1a1a; line-height: 1.15;
  margin-bottom: 4px;
}
.slide-sub {
  font-size: 1.6rem; font-weight: 400;
  color: #444; margin-bottom: 28px;
}
.btn-shop-now {
  display: inline-block;
  border: 1.5px solid #444; color: #222;
  padding: 11px 28px; font-size: 13px;
  font-weight: 700; letter-spacing: 0.08em;
  text-transform: uppercase; text-decoration: none;
  background: transparent; cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
}
.btn-shop-now:hover { background: #222; color: #fff; }

/* Yarn visual */
.yarn-visual {
  position: relative; width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.yarn-ball-svg {
  width: min(300px, 60%); height: auto;
  filter: drop-shadow(0 20px 40px rgba(0,0,0,0.15));
}
.yarn-product-label {
  position: absolute; bottom: 30%; right: 30%;
  background: rgba(255,255,255,0.9);
  padding: 8px 14px; border-radius: 4px;
  font-size: 11px; font-weight: 700;
  color: #333; letter-spacing: 0.05em;
  text-transform: uppercase;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transform: rotate(-5deg);
  line-height: 1.3;
}

/* Carousel dots */
.carousel-dots {
  position: absolute; bottom: 18px;
  left: 50%; transform: translateX(-50%);
  display: flex; gap: 8px; z-index: 10;
}
.cdot {
  width: 10px; height: 10px; border-radius: 50%;
  background: rgba(255,255,255,0.5);
  cursor: pointer; border: 1.5px solid rgba(255,255,255,0.8);
  transition: all 0.2s;
}
.cdot.active { background: #fff; transform: scale(1.2); }

/* ─── SECTION COMMON ─── */
.section-wrap {
  max-width: 1400px; margin: 0 auto;
  padding: 48px 32px;
}
.section-label {
  font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase;
  color: #999; font-weight: 700; margin-bottom: 28px;
}

/* ─── CATEGORIES CIRCLES ─── */
.categories-section { border-bottom: 1px solid #eee; }
.categories-row {
  display: flex; gap: 0;
  overflow-x: auto;
  padding-bottom: 8px;
  scrollbar-width: none;
}
.categories-row::-webkit-scrollbar { display: none; }
.cat-circle-item {
  display: flex; flex-direction: column;
  align-items: center; gap: 12px;
  flex: 1; min-width: 150px;
  cursor: pointer; padding: 0 16px;
  text-decoration: none;
}
.cat-circle-img {
  width: 140px; height: 140px; border-radius: 50%;
  overflow: hidden; position: relative;
  background: #f5f5f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 60px;
  border: 2px solid transparent;
  transition: border-color 0.2s, transform 0.2s;
}
.cat-circle-item:hover .cat-circle-img {
  border-color: #2dd4a0;
  transform: scale(1.04);
}
/* Real photo backgrounds for circles */
.ci-1 { background: linear-gradient(135deg, #f0ece4 0%, #e0d8cc 100%); }
.ci-2 { background: linear-gradient(135deg, #f8f0e0 0%, #e8d8b8 100%); }
.ci-3 { background: linear-gradient(135deg, #e8f0e8 0%, #c8e0c8 100%); }
.ci-4 { background: linear-gradient(135deg, #3a3a3a 0%, #555 100%); }
.ci-5 { background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%); }
.ci-6 { background: linear-gradient(135deg, #f8f8e8 0%, #ece8c8 100%); }

.cat-circle-name {
  font-size: 13px; font-weight: 600; color: #222;
  text-align: center; line-height: 1.3;
}

/* ─── FEATURED PRODUCTS ─── */
.products-section { background: #fff; border-bottom: 1px solid #eee; }
.products-section-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px;
}
.section-title-big {
  font-size: 22px; font-weight: 800; color: #111;
}
.view-all-link {
  font-size: 13px; font-weight: 600; color: #2dd4a0;
  text-decoration: none; letter-spacing: 0.03em;
}
.view-all-link:hover { text-decoration: underline; }

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.product-card {
  border: 1px solid #eee; border-radius: 6px;
  overflow: hidden; background: #fff;
  transition: box-shadow 0.2s, transform 0.2s;
  cursor: pointer; position: relative;
}
.product-card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.product-img-wrap {
  position: relative; background: #f8f8f8;
  aspect-ratio: 1; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
}
.product-img-inner {
  font-size: 80px; transition: transform 0.4s;
  display: flex; align-items: center; justify-content: center;
  width: 100%; height: 100%;
}
.product-card:hover .product-img-inner { transform: scale(1.05); }

/* Product image placeholder backgrounds */
.pi-white { background: #f9f9f7; }
.pi-cream { background: #fdf8f0; }
.pi-sage { background: #f0f5ee; }
.pi-blue { background: #eef2f8; }
.pi-pink { background: #fdf0f3; }
.pi-lilac { background: #f3f0fd; }
.pi-peach { background: #fdf3ee; }
.pi-mint { background: #eef8f5; }

/* Fake product image using CSS yarn ball */
.yarn-img {
  width: 80%; height: 80%;
  display: flex; align-items: center; justify-content: center;
}
.yarn-img svg { width: 100%; height: 100%; }

.product-body { padding: 14px; }
.product-name {
  font-size: 13px; font-weight: 600; color: #222;
  line-height: 1.4; margin-bottom: 8px;
  display: -webkit-box; -webkit-line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden;
}
.product-price {
  font-size: 17px; font-weight: 800; color: #111;
  margin-bottom: 8px;
}
.product-price .price-currency { font-size: 13px; }
.product-reviews {
  display: flex; align-items: center; gap: 6px;
  margin-bottom: 12px;
}
.stars { color: #f5a623; font-size: 13px; }
.review-count { font-size: 12px; color: #888; }
.btn-add-cart {
  width: 100%; background: #2dd4a0; color: #fff;
  border: none; padding: 11px;
  font-size: 13px; font-weight: 700;
  cursor: pointer; font-family: inherit;
  border-radius: 4px;
  transition: background 0.2s;
  letter-spacing: 0.02em;
}
.btn-add-cart:hover { background: #1ab890; }

.product-badge {
  position: absolute; top: 10px; left: 10px;
  background: #e74c3c; color: #fff;
  font-size: 11px; font-weight: 700; letter-spacing: 0.04em;
  padding: 3px 8px; border-radius: 3px;
}
.badge-new { background: #2dd4a0; }
.badge-sale { background: #e74c3c; }
.badge-pop { background: #f5a623; }

/* ─── SIDEBAR + PRODUCT LIST (All Products style) ─── */
.listing-section { background: #fafafa; }
.listing-wrap {
  max-width: 1400px; margin: 0 auto;
  padding: 32px 32px;
  display: grid; grid-template-columns: 220px 1fr; gap: 28px;
}
.sidebar {
  background: #fff; border: 1px solid #eee; border-radius: 6px;
  padding: 20px; align-self: start;
}
.sidebar h3 {
  font-size: 15px; font-weight: 800; margin-bottom: 16px; color: #111;
}
.menu-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 9px 0; border-bottom: 1px solid #f0f0f0;
  font-size: 13px; color: #444; cursor: pointer;
  transition: color 0.15s;
}
.menu-item:hover { color: #2dd4a0; }
.menu-item.active { font-weight: 700; color: #111; }
.menu-item .arrow2 { font-size: 10px; color: #bbb; }

.listing-main {}
.listing-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
}
.listing-title { font-size: 22px; font-weight: 800; color: #111; }
.listing-controls {
  display: flex; align-items: center; gap: 16px;
}
.listing-breadcrumb {
  font-size: 13px; color: #2dd4a0; margin-bottom: 16px; font-weight: 600;
}
.listing-meta {
  display: flex; align-items: center; justify-content: space-between;
  background: #fff; border: 1px solid #eee; border-radius: 4px;
  padding: 10px 16px; margin-bottom: 20px;
  font-size: 13px; color: #555;
}
.listing-meta-right { display: flex; align-items: center; gap: 20px; }
select.ctrl-select {
  border: 1px solid #ddd; border-radius: 4px;
  padding: 6px 10px; font-size: 12px; color: #444;
  cursor: pointer; font-family: inherit;
}
.view-btns { display: flex; gap: 4px; }
.view-btn {
  width: 30px; height: 30px; border: 1px solid #ddd;
  border-radius: 4px; display: flex; align-items: center;
  justify-content: center; cursor: pointer; font-size: 14px;
  color: #888; transition: all 0.15s;
}
.view-btn.active { border-color: #2dd4a0; color: #2dd4a0; background: #f0fdf9; }

.listing-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
}

/* ─── PROMO BANNER ─── */
.promo-banner {
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
  padding: 48px 32px; text-align: center;
}
.promo-inner { max-width: 600px; margin: 0 auto; }
.promo-title {
  font-family: 'Dancing Script', cursive;
  font-size: 2.5rem; color: #fff; margin-bottom: 8px;
}
.promo-sub { font-size: 14px; color: #aaa; margin-bottom: 24px; line-height: 1.7; }
.promo-codes { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 24px; }
.promo-code {
  background: rgba(45,212,160,0.15); border: 1px solid #2dd4a0;
  color: #2dd4a0; padding: 6px 16px; border-radius: 4px;
  font-size: 12px; font-weight: 700; letter-spacing: 0.06em;
}
.btn-promo {
  background: #2dd4a0; color: #fff; border: none;
  padding: 13px 32px; font-size: 14px; font-weight: 700;
  cursor: pointer; font-family: inherit; border-radius: 4px;
  transition: background 0.2s;
}
.btn-promo:hover { background: #1ab890; }

/* ─── BRANDS ─── */
.brands-section { border-bottom: 1px solid #eee; }
.brands-row {
  display: flex; gap: 32px; align-items: center;
  overflow-x: auto; padding-bottom: 4px;
  scrollbar-width: none;
}
.brands-row::-webkit-scrollbar { display: none; }
.brand-item {
  flex-shrink: 0; padding: 12px 20px;
  border: 1px solid #eee; border-radius: 8px;
  font-size: 14px; font-weight: 700; color: #555;
  cursor: pointer; transition: all 0.2s; min-width: 100px; text-align: center;
}
.brand-item:hover { border-color: #2dd4a0; color: #2dd4a0; }

/* ─── FOOTER ─── */
footer {
  background: #111; color: #aaa;
  padding: 48px 32px 24px;
}
.footer-top {
  max-width: 1400px; margin: 0 auto;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 40px; margin-bottom: 40px;
}
.footer-logo {
  font-family: 'Nunito Sans', sans-serif;
  font-size: 1.8rem; font-weight: 800;
  color: #2dd4a0; font-style: italic;
  margin-bottom: 14px; display: block;
}
.footer-desc { font-size: 13px; line-height: 1.8; color: #777; max-width: 280px; }
.footer-col h4 {
  font-size: 13px; font-weight: 800; color: #fff;
  margin-bottom: 16px; letter-spacing: 0.05em; text-transform: uppercase;
}
.footer-col ul { list-style: none; }
.footer-col li { margin-bottom: 9px; }
.footer-col a {
  font-size: 13px; color: #777; text-decoration: none;
  transition: color 0.15s;
}
.footer-col a:hover { color: #2dd4a0; }
.footer-bottom {
  max-width: 1400px; margin: 0 auto;
  border-top: 1px solid #222; padding-top: 20px;
  display: flex; justify-content: space-between; align-items: center;
}
.footer-bottom p { font-size: 12px; color: #555; }
.payment-icons { display: flex; gap: 8px; }
.pay-icon {
  background: #222; border: 1px solid #333;
  padding: 4px 10px; border-radius: 4px;
  font-size: 11px; color: #666; font-weight: 700;
}

/* ─── RESPONSIVE ─── */
@media (max-width: 1100px) {
  .products-grid { grid-template-columns: repeat(3, 1fr); }
  .listing-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 800px) {
  .header-main { padding: 12px 16px; }
  .listing-wrap { grid-template-columns: 1fr; }
  .sidebar { display: none; }
  .products-grid { grid-template-columns: repeat(2, 1fr); }
  .listing-grid { grid-template-columns: repeat(2, 1fr); }
  .footer-top { grid-template-columns: 1fr 1fr; }
  .slide-text-wrap { width: 260px; padding: 32px 24px; }
  .slide-main { font-size: 1.6rem; }
  .nav-inner { overflow-x: auto; }
}
@media (max-width: 560px) {
  .products-grid { grid-template-columns: repeat(2, 1fr); }
  .cat-circle-item { min-width: 110px; }
  .cat-circle-img { width: 100px; height: 100px; }
}
</style>
</head>
<body>

<!-- ANNOUNCEMENT BAR -->
<div class="announcement-bar">
  <span>CozYarn sẽ giúp bạn xây cả thể giới bằng len</span> Vận chuyển toàn cầu!
</div>

<!-- HEADER -->
<header>
  <div class="header-main">
    <a href="#" class="logo">CozYarn</a>
    <div class="search-wrap">
      <input class="search-input" type="text" placeholder="Search...">
      <button class="search-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      </button>
    </div>
    <div class="header-right">
      <div class="header-country">
        Country/region<br><strong>Vietnam (VND ₫) ▾</strong>
      </div>
      <div class="header-divider"></div>
      <div class="header-account">
        Đăng kí / Đăng nhập<br><strong>Tài khoản của tôi</strong>
      </div>
      <div class="header-divider"></div>
      <a href="#" class="cart-btn">
        <div class="cart-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#222" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          <span class="cart-count-badge" id="cartBadge">0</span>
        </div>
        <span>Giỏ hàng của tôi</span>
      </a>
    </div>
  </div>
</header>

<!-- MAIN NAV -->
<nav class="main-nav">
  <div class="nav-inner">
    @if(isset($navOptions) && is_array($navOptions))
      @foreach($navOptions as $nav)
        <div class="nav-item">
          <a href="{{ $nav['url'] ?? '#' }}" class="nav-link">
            {{ $nav['label'] }}
            @if(!empty($nav['children'])) <span class="arrow">▾</span>@endif
          </a>
          @if(!empty($nav['children']))
            <div class="nav-dropdown">
              @foreach($nav['children'] as $child)
                <a href="{{ $child['url'] ?? '#' }}">{{ $child['label'] }}</a>
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    @endif
  </div>
</nav>

<!-- HERO CAROUSEL -->
<div class="hero">
  <div class="carousel-track" id="heroTrack">    
  <div class="carousel-dots" id="heroDots">
    <div class="cdot active" onclick="goSlide(0)"></div>
    <div class="cdot" onclick="goSlide(1)"></div>
    <div class="cdot" onclick="goSlide(2)"></div>
    <div class="cdot" onclick="goSlide(3)"></div>
    <div class="cdot" onclick="goSlide(4)"></div>
    <div class="cdot" onclick="goSlide(5)"></div>
  </div>
</div>

<!-- CATEGORIES CIRCLES -->
<section class="categories-section">
  <div class="section-wrap">
    <div class="section-label">CATEGORIES</div>
    <div class="categories-row">
      @if(isset($categories) && count($categories))
        @foreach($categories as $cat)
          <a href="{{ $cat->url ?? '#' }}" class="cat-circle-item">
            <div class="cat-circle-img" style="background:{{ $cat->bg ?? '#f5f5f5' }}">
              @if(!empty($cat->icon_svg))
                {!! $cat->icon_svg !!}
              @else
                <span style="font-size:60px;">🧶</span>
              @endif
            </div>
            <span class="cat-circle-name">{{ $cat->name }}</span>
          </a>
        @endforeach
      @endif
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="products-section">
  

<!-- ALL PRODUCTS — sidebar layout -->
<section class="listing-section">
  <div class="listing-wrap">

    <!-- SIDEBAR -->
    <div class="sidebar">
      <h3>Menu</h3>
      
    </div>

    <!-- LISTING MAIN -->
    <div class="listing-main">
      <div class="listing-header">
        <div class="listing-title">All Products</div>
      </div>
      <div class="listing-breadcrumb">All Products</div>
      <div class="listing-meta">
        <span>Showing 1 – 24 of 9326 products</span>
        <div class="listing-meta-right">
          <label style="font-size:13px;color:#555;">Display:
            <select class="ctrl-select">
              <option>24 per page</option>
              <option>48 per page</option>
              <option>96 per page</option>
            </select>
          </label>
          <label style="font-size:13px;color:#555;">Sort by:
            <select class="ctrl-select">
              <option>Featured</option>
              <option>Price: Low to High</option>
              <option>Price: High to Low</option>
              <option>Newest</option>
              <option>Best Sellers</option>
            </select>
          </label>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:#555;">View</span>
            <div class="view-btns">
              <div class="view-btn active">⊞</div>
              <div class="view-btn">☰</div>
            </div>
          </div>
        </div>
      </div>

      <div class="listing-grid">
</section>

<!-- BRANDS -->
<section class="brands-section">
  <div class="section-wrap">
    <div class="section-label">THƯƠNG HIỆU</div>
    <div class="brands-row">
    </div>
  </div>
</section>
<section class="blogs-section">
    <div class="section-wrap">
        <div class="section-label">BÀI ĐĂNG</div>
    </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-top">
    <div>
      <a href="#" class="footer-logo">COZYARN</a>
      <p class="footer-desc">Tống Gia Bảo cute đẹp trai no1 thế giới</p>
    </div>
    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="#">Yarn</a></li>
        <!-- <li><a href="#">Tools & Accessories</a></li>
        <li><a href="#">Sewing & Embroidery</a></li>
        <li><a href="#">Amigurumi</a></li>
        <li><a href="#">Home & Living</a></li>
        <li><a href="#">Outlet</a></li> -->
      </ul>
    </div>
    <div class="footer-col">
      <h4>Help</h4>
      <ul>
        <li><a href="#">Thông tin vận chuyển</a></li>
        <li><a href="#">Chính sách hoàn hàng</a></li>
        <li><a href="#">FAQs</a></li>
        <li><a href="#">Theo dõi đơn hàng</a></li>
        <li><a href="#">Liên hệ với shop</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="#">Về chúng tôi</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Chính sách</a></li>
        <li><a href="#">Điều khoản dịch vụ</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 CozYarn. All rights reserved.</p>
    <div class="payment-icons">
      <div class="pay-icon">VISA</div>
      <div class="pay-icon">MOMO</div>
      <div class="pay-icon">PAYPAL</div>
    </div>
  </div>
</footer>

<script>
// ─── HERO CAROUSEL ───
let cur = 0;
const total = 6;
let timer;

function goSlide(n) {
  cur = n;
  document.getElementById('heroTrack').style.transform = `translateX(-${cur * 100}%)`;
  document.querySelectorAll('.cdot').forEach((d, i) => d.classList.toggle('active', i === cur));
}
function nextSlide() {
  cur = (cur + 1) % total;
  goSlide(cur);
}
function resetTimer() {
  clearInterval(timer);
  timer = setInterval(nextSlide, 5000);
}
resetTimer();

// Swipe support
let startX = 0;
document.querySelector('.hero').addEventListener('touchstart', e => startX = e.touches[0].clientX);
document.querySelector('.hero').addEventListener('touchend', e => {
  const dx = startX - e.changedTouches[0].clientX;
  if (Math.abs(dx) > 50) { cur = (cur + (dx > 0 ? 1 : -1) + total) % total; goSlide(cur); resetTimer(); }
});

// ─── CART ───
let cartN = 0;
function addCart() {
  cartN++;
  document.getElementById('cartBadge').textContent = cartN;
  document.getElementById('cartBadge').style.background = '#e74c3c';
  setTimeout(() => document.getElementById('cartBadge').style.background = '#2dd4a0', 600);
}

// ─── SCROLL REVEAL ───
const io = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.opacity = '1';
      e.target.style.transform = 'translateY(0)';
      io.unobserve(e.target);
    }
  });
}, { threshold: 0.08 });

document.querySelectorAll('.product-card, .cat-circle-item, .brand-item').forEach((el, i) => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = `opacity 0.4s ease ${(i % 8) * 0.07}s, transform 0.4s ease ${(i % 8) * 0.07}s`;
  io.observe(el);
});

// ─── VIEW TOGGLE ───
document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});
</script>
</body>
</html>