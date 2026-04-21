@php
    $isHome = ($isHome ?? false);
    $homePrefix = $isHome ? '' : '/';
@endphp
<header class="site-header">
    <div class="site-header__inner">
        <div class="top-nav">
            <a href="/" class="brand-wrap">
                <img class="brand-avatar" src="/images/avartar.jpg" alt="CozyYarn">
                <span class="brand">CozyYarn</span>
            </a>
            <nav class="menu" data-nav>
                <a href="{{ $homePrefix }}#home" @if($isHome) data-section="home" class="active" @endif>Home</a>
                <a href="/shop">Shop</a>
                <a href="/blog">Blog</a>
                <a href="{{ $homePrefix }}#about" @if($isHome) data-section="about" @endif>About us</a>
                <a href="{{ $homePrefix }}#contact" @if($isHome) data-section="contact" @endif>Contact</a>
            </nav>
            <div class="header-actions">
                <a href="#" class="action-pill">
                    <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
                    </svg>
                    <span>Tìm kiếm</span>
                </a>
                <a href="#" class="action-pill">
                    <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"></path>
                    </svg>
                    <span>Tài khoản</span>
                </a>
                <a href="#" class="action-pill">
                    <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="9" cy="20" r="1.5"></circle>
                        <circle cx="18" cy="20" r="1.5"></circle>
                        <path d="M3 4h2l2.2 10.3a1 1 0 0 0 1 .7h9.8a1 1 0 0 0 1-.8L21 8H7"></path>
                    </svg>
                    <span>Giỏ hàng</span>
                </a>
            </div>
        </div>
    </div>
</header>
