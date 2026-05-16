@php
    $active ??= '';
    // Số notification chưa-đọc cho từng nhóm — đọc từ shared AdminInbox (file cache),
    // không phụ thuộc session của admin hiện tại nên mọi admin đều thấy cùng số.
    $unreadOrders   = \App\Support\AdminInbox::unreadCount('order_new')
                    + \App\Support\AdminInbox::unreadCount('order_paid');
    $unreadMessages = \App\Support\AdminInbox::unreadCount('message');
@endphp
<style>
.admin-nav__item{position:relative}
.admin-nav__badge{
    margin-left:auto;background:#dc2626;color:#fff;border-radius:999px;
    font-size:11px;font-weight:700;line-height:1;padding:3px 7px;min-width:18px;
    text-align:center;display:inline-block
}
.admin-nav__item.is-active .admin-nav__badge{background:#fff;color:#dc2626}
</style>
<aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
        <img src="/images/avartar.jpg" alt="CozyYarn">
        <div>
            <strong>CozyYarn</strong>
            <small>Quản trị</small>
        </div>
    </div>

    <nav class="admin-nav">
        <span class="admin-nav__group">Tổng quan</span>
        <a href="{{ route('admin.dashboard') }}" class="admin-nav__item @if($active === 'dashboard') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg>
            Dashboard
        </a>

        <span class="admin-nav__group">Kinh doanh</span>
        <a href="{{ route('admin.orders.index') }}" class="admin-nav__item @if($active === 'orders') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
            Đơn hàng
            @if($unreadOrders > 0)
                <span class="admin-nav__badge" aria-label="{{ $unreadOrders }} đơn hàng mới">{{ $unreadOrders > 99 ? '99+' : $unreadOrders }}</span>
            @endif
        </a>
        <a href="{{ route('admin.products.index') }}" class="admin-nav__item @if($active === 'products') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 8l8-4 8 4v8l-8 4-8-4V8zM4 8l8 4 8-4M12 12v8"/></svg>
            Sản phẩm
        </a>
        <a href="{{ route('admin.categories.index') }}" class="admin-nav__item @if($active === 'categories') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
            Danh mục
        </a>

        <span class="admin-nav__group">Nội dung</span>
        <a href="{{ route('admin.blog.index') }}" class="admin-nav__item @if($active === 'blog') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4zM4 9h16M8 13h6M8 17h4"/></svg>
            Blog
        </a>
        <a href="{{ route('admin.notifications.index') }}" class="admin-nav__item @if($active === 'notifications') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 10a6 6 0 1 1 12 0v4l2 3H4l2-3z" stroke-linejoin="round"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
            Thông báo
        </a>

        <span class="admin-nav__group">Khách hàng</span>
        <a href="{{ route('admin.users.index') }}" class="admin-nav__item @if($active === 'users') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/></svg>
            Tài khoản
        </a>
        <a href="{{ route('admin.chat.index') }}" class="admin-nav__item @if($active === 'chat') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H8l-4 4V5z" stroke-linejoin="round"/></svg>
            Tin nhắn
            @if($unreadMessages > 0)
                <span class="admin-nav__badge" aria-label="{{ $unreadMessages }} tin nhắn mới">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
            @endif
        </a>

        <span class="admin-nav__group">Hệ thống</span>
        <a href="{{ route('admin.skin.index') }}" class="admin-nav__item @if($active === 'skin') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 0 0 18M3 12h18"/></svg>
            Giao diện
        </a>
        <a href="{{ route('admin.plugins.index') }}" class="admin-nav__item @if($active === 'plugins') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 2v4M15 2v4M7 8h10v6a5 5 0 0 1-10 0V8z"/><path d="M12 19v3"/></svg>
            Plugin
        </a>
        <a href="/" target="_blank" rel="noopener noreferrer" class="admin-nav__item" title="Mở trang shop trong tab mới">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 3h6v6M21 3l-9 9M10 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/></svg>
            Xem shop
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="admin-nav__item admin-nav__item--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17l5-5-5-5M20 12H9M12 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/></svg>
                Đăng xuất
            </button>
        </form>
    </nav>

    <div style="margin-top:auto;padding:14px 16px;border-top:1px solid #f5d6e3;font-size:11px;color:#9d7a8a;text-align:center">
        <strong>CozyYarn</strong> v{{ \App\Support\AppVersion::version() }}
        · build <code style="font-size:10px">{{ \App\Support\AppVersion::commit() }}</code>
    </div>
</aside>
