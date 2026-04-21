@php
    $isHome      = ($isHome ?? false);
    $homePrefix  = $isHome ? '' : '/';
    $cartCount   = \App\Support\Cart::count();
@endphp

<header class="site-header" data-header>
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

                {{-- SEARCH --}}
                <button type="button" class="action-pill" data-search-toggle aria-label="Tìm kiếm">
                    <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
                    </svg>
                    <span>Tìm kiếm</span>
                </button>

                {{-- ACCOUNT --}}
                @auth
                    <div class="action-menu" data-dropdown>
                        <button type="button" class="action-pill" data-dropdown-toggle>
                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"></path>
                            </svg>
                            <span>{{ \Illuminate\Support\Str::limit(auth()->user()->name, 12, '') }}</span>
                            <svg class="action-caret" viewBox="0 0 12 12" aria-hidden="true">
                                <path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <div class="dropdown-user">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                            </div>
                            <a href="{{ route('user.orders') }}" class="dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                                Đơn hàng của tôi
                            </a>
                            <a href="{{ route('user.profile') }}" class="dropdown-item" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/></svg>
                                Thông tin tài khoản
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="dropdown-form">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-item--danger" role="menuitem">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17l5-5-5-5M20 12H9M12 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/></svg>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="action-pill">
                        <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"></path>
                        </svg>
                        <span>Tài khoản</span>
                    </a>
                @endauth

                {{-- CART --}}
                <a href="{{ route('cart.index') }}" class="action-pill action-pill--cart">
                    <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="9" cy="20" r="1.5"></circle>
                        <circle cx="18" cy="20" r="1.5"></circle>
                        <path d="M3 4h2l2.2 10.3a1 1 0 0 0 1 .7h9.8a1 1 0 0 0 1-.8L21 8H7"></path>
                    </svg>
                    <span>Giỏ hàng</span>
                    @if($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- SEARCH OVERLAY PANEL --}}
        <div class="search-panel" data-search-panel>
            <form method="GET" action="{{ route('search') }}" class="search-panel__form">
                <svg class="search-panel__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="16.65" y1="16.65" x2="21" y2="21"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Tìm len, kim, kit, phụ kiện..."
                       autocomplete="off"
                       data-search-input>
                <button type="submit" class="search-panel__submit">Tìm</button>
                <button type="button" class="search-panel__close" data-search-close aria-label="Đóng">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>

{{-- FLASH TOAST (thêm giỏ / xoá...) --}}
@if(session('cart_flash'))
<div class="cart-toast" data-toast>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M8 12l3 3 5-6"/>
    </svg>
    <span>{{ session('cart_flash') }}</span>
</div>
@endif

<script>
(() => {
    const header = document.querySelector('[data-header]');
    if (!header) return;

    // ---------- Search toggle ----------
    const searchBtn   = header.querySelector('[data-search-toggle]');
    const searchPanel = header.querySelector('[data-search-panel]');
    const searchInput = header.querySelector('[data-search-input]');
    const searchClose = header.querySelector('[data-search-close]');

    const openSearch  = () => {
        searchPanel.classList.add('is-open');
        setTimeout(() => searchInput?.focus(), 50);
    };
    const closeSearch = () => searchPanel.classList.remove('is-open');

    searchBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        searchPanel.classList.contains('is-open') ? closeSearch() : openSearch();
    });
    searchClose?.addEventListener('click', closeSearch);

    // Click ra ngoài để đóng search
    document.addEventListener('click', (e) => {
        if (!searchPanel.classList.contains('is-open')) return;
        if (searchPanel.contains(e.target) || searchBtn.contains(e.target)) return;
        closeSearch();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSearch();
    });

    // ---------- Account dropdown ----------
    header.querySelectorAll('[data-dropdown]').forEach(wrap => {
        const toggle = wrap.querySelector('[data-dropdown-toggle]');
        toggle?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            wrap.classList.toggle('is-open');
        });
    });
    document.addEventListener('click', (e) => {
        header.querySelectorAll('[data-dropdown].is-open').forEach(wrap => {
            if (!wrap.contains(e.target)) wrap.classList.remove('is-open');
        });
    });

    // ---------- Auto-hide toast ----------
    const toast = document.querySelector('[data-toast]');
    if (toast) {
        setTimeout(() => toast.classList.add('is-leaving'), 2500);
        setTimeout(() => toast.remove(), 3200);
    }
})();
</script>
