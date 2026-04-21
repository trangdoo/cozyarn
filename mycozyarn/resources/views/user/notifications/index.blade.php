@extends('layouts.public')

@section('title', 'Thông báo — CozyYarn')

@php
    $iconMap = [
        'order-placed'     => ['bg' => '#fff6cc', 'fg' => '#9a7a1f', 'svg' => '<path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/>'],
        'order-confirmed'  => ['bg' => '#e0f0ff', 'fg' => '#2c5580', 'svg' => '<path d="M5 12l5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/>'],
        'order-shipping'   => ['bg' => '#fde4ee', 'fg' => '#b55a82', 'svg' => '<rect x="3" y="8" width="13" height="9" rx="1"/><path d="M16 11h4l2 3v3h-6"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>'],
        'order-delivered'  => ['bg' => '#c3e8d5', 'fg' => '#3d7a52', 'svg' => '<path d="M4 12l5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/>'],
        'order-cancelled'  => ['bg' => '#ffe0e0', 'fg' => '#a63652', 'svg' => '<circle cx="12" cy="12" r="9"/><path d="M8 8l8 8M16 8l-8 8" stroke-linecap="round"/>'],
        'order-returned'   => ['bg' => '#e4dcf5', 'fg' => '#5b4ba5', 'svg' => '<path d="M3 7l3-3 3 3M6 4v10a4 4 0 0 0 4 4h10" stroke-linecap="round" stroke-linejoin="round"/>'],
        'order-received'   => ['bg' => '#d4efdb', 'fg' => '#2f6a42', 'svg' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/>'],
        'promo-discount'   => ['bg' => '#fff0e3', 'fg' => '#b15e1f', 'svg' => '<path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7" cy="7" r="1.4" fill="currentColor"/>'],
        'promo-ship'       => ['bg' => '#fde4ee', 'fg' => '#b55a82', 'svg' => '<rect x="3" y="8" width="13" height="9" rx="1"/><path d="M16 11h4l2 3v3h-6"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>'],
        'promo-new'        => ['bg' => '#e0f0ff', 'fg' => '#2c5580', 'svg' => '<path d="M12 2l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/>'],
        'info'             => ['bg' => '#fff6fa', 'fg' => '#b55a82', 'svg' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>'],
    ];
    $getIcon = fn($key) => $iconMap[$key] ?? $iconMap['info'];
@endphp

@section('content')
<section class="notif-page">
    <div class="notif-page__inner">
        <header class="notif-page__head">
            <div>
                <span class="section-chip">Thông báo</span>
                <h1 class="notif-page__title">Thông báo của tôi</h1>
                <p class="notif-page__sub">
                    @if($unreadCount > 0)
                        Bạn có <strong>{{ $unreadCount }}</strong> thông báo chưa đọc.
                    @else
                        Tất cả thông báo đều đã được đọc.
                    @endif
                </p>
            </div>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('user.notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="cart-btn cart-btn--ghost">Đánh dấu đã đọc tất cả</button>
                </form>
            @endif
        </header>

        @if(session('cart_flash'))
            <div class="cart-alert cart-alert--success" style="margin-bottom:18px">{{ session('cart_flash') }}</div>
        @endif

        <nav class="notif-tabs" aria-label="Lọc thông báo">
            <a href="{{ route('user.notifications.index') }}"
               class="notif-tab @if($activeFilter === 'all') is-active @endif">
                Tất cả <span class="notif-tab__count">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('user.notifications.index', ['type' => 'order']) }}"
               class="notif-tab @if($activeFilter === 'order') is-active @endif">
                Đơn hàng <span class="notif-tab__count">{{ $counts['order'] }}</span>
            </a>
            <a href="{{ route('user.notifications.index', ['type' => 'promo']) }}"
               class="notif-tab @if($activeFilter === 'promo') is-active @endif">
                Khuyến mãi <span class="notif-tab__count">{{ $counts['promo'] }}</span>
            </a>
        </nav>

        @if(count($notifications) === 0)
            <div class="cart-empty">
                <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                    <circle cx="60" cy="60" r="52" fill="#fff0e3"/>
                    <path d="M42 50a18 18 0 0 1 36 0v16l6 8H36l6-8z" stroke="#d9a573" stroke-width="3" fill="none" stroke-linejoin="round"/>
                    <path d="M54 80a6 6 0 0 0 12 0" stroke="#d9a573" stroke-width="3" fill="none" stroke-linecap="round"/>
                </svg>
                <h3>Không có thông báo nào</h3>
                <p>Khi có cập nhật về đơn hàng hoặc chương trình khuyến mãi, thông báo sẽ xuất hiện ở đây.</p>
                <a href="/shop" class="cart-btn cart-btn--primary">Khám phá shop</a>
            </div>
        @else
            <div class="notif-list">
                @foreach($notifications as $n)
                    @php
                        $icon = $getIcon($n['icon'] ?? 'info');
                        $href = route('user.notifications.open', ['id' => $n['id']]);
                    @endphp
                    <a href="{{ $href }}" class="notif-item @if(empty($n['is_read'])) is-unread @endif">
                        <div class="notif-item__icon" style="background:{{ $icon['bg'] }};color:{{ $icon['fg'] }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                {!! $icon['svg'] !!}
                            </svg>
                        </div>
                        <div class="notif-item__body">
                            <div class="notif-item__head">
                                <strong>{{ $n['title'] }}</strong>
                                <span class="notif-item__time">
                                    {{ \Carbon\Carbon::parse($n['created_at'])->diffForHumans() }}
                                </span>
                            </div>
                            <p class="notif-item__content">{{ $n['content'] }}</p>
                            <span class="notif-item__type notif-item__type--{{ $n['type'] }}">
                                @if(($n['type'] ?? '') === 'order')
                                    Đơn hàng
                                @elseif(($n['type'] ?? '') === 'promo')
                                    Khuyến mãi
                                @else
                                    Thông báo
                                @endif
                            </span>
                        </div>
                        @if(empty($n['is_read']))
                            <span class="notif-item__dot" aria-label="Chưa đọc"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
