<header class="admin-topbar">
    <button type="button" class="admin-topbar__menu" data-admin-menu-toggle aria-label="Mở menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>
    </button>

    <div class="admin-topbar__title">
        @yield('page_title', 'Quản trị')
    </div>

    <div class="admin-topbar__actions">
        <a href="/" class="admin-topbar__link" title="Xem trang shop">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 3h6v6M21 3l-9 9M10 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/></svg>
            Xem shop
        </a>
        <div class="admin-topbar__user">
            <div class="admin-topbar__avatar">
                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="admin-topbar__user-info">
                <strong>{{ auth()->user()->name }}</strong>
                <small>Quản trị viên</small>
            </div>
        </div>
    </div>
</header>
