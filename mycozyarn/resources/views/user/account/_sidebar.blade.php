@php $active ??= ''; @endphp
<aside class="acc-sidebar">
    <div class="acc-user">
        <div class="acc-user__avatar">
            @if(auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
            @else
                <span>{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="acc-user__info">
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ auth()->user()->email }}</small>
        </div>
    </div>
    <nav class="acc-nav">
        <a href="{{ route('user.profile') }}" class="acc-nav-item @if($active === 'profile') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/></svg>
            Thông tin tài khoản
        </a>
        <a href="{{ route('user.orders') }}" class="acc-nav-item @if($active === 'orders') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
            Đơn đang xử lý
        </a>
        <a href="{{ route('user.orders.completed') }}" class="acc-nav-item acc-nav-item--sub @if($active === 'completed') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Đơn hoàn tất
        </a>
        <a href="{{ route('user.orders.cancelled') }}" class="acc-nav-item acc-nav-item--sub @if($active === 'cancelled') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 8l8 8M16 8l-8 8" stroke-linecap="round"/></svg>
            Đơn đã huỷ
        </a>
        <a href="{{ route('user.orders.returned') }}" class="acc-nav-item acc-nav-item--sub @if($active === 'returned') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7l3-3 3 3M6 4v10a4 4 0 0 0 4 4h10" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Trả hàng / Hoàn tiền
        </a>
        <a href="{{ route('user.reviews') }}" class="acc-nav-item @if($active === 'reviews') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l3.1 6.5 7.1 1-5.2 4.9 1.3 7.1L12 18l-6.3 3.5 1.3-7.1L1.8 9.5l7.1-1z"/></svg>
            Đánh giá của tôi
        </a>
        <a href="{{ route('blog.liked') }}" class="acc-nav-item @if($active === 'blog_liked') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-4.5-9.5-9A5 5 0 0 1 12 6a5 5 0 0 1 9.5 6C19 16.5 12 21 12 21z" stroke-linejoin="round"/></svg>
            Bài viết đã tim
        </a>
        <form method="POST" action="{{ route('logout') }}" class="acc-nav-form">
            @csrf
            <button type="submit" class="acc-nav-item acc-nav-item--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17l5-5-5-5M20 12H9M12 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/></svg>
                Đăng xuất
            </button>
        </form>
    </nav>
</aside>
