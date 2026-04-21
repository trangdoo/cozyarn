@extends('layouts.public')

@php
    $activeTab ??= 'active';
    $tabTitles = [
        'active'    => 'Đơn đang xử lý',
        'completed' => 'Đơn đã hoàn tất',
        'cancelled' => 'Đơn đã huỷ',
        'returned'  => 'Đơn trả hàng & hoàn tiền',
    ];
    $pageTitle = $tabTitles[$activeTab] ?? 'Đơn hàng của tôi';
@endphp

@section('title', "{$pageTitle} — CozyYarn")

@php
    use App\Support\OrderTimeline;
    $statusMap = [
        'pending'          => ['Chờ xác nhận',         '#fff6cc', '#9a7a1f'],
        'placed'           => ['Đã đặt hàng',          '#fff6cc', '#9a7a1f'],
        'confirmed'        => ['Chờ lấy hàng',         '#e0f0ff', '#2c5580'],
        'shipping'         => ['Chờ giao hàng',        '#fde4ee', '#b55a82'],
        'delivered'        => ['Đã giao — chờ xác nhận','#c3e8d5', '#3d7a52'],
        'received'         => ['Hoàn tất',             '#d4efdb', '#2f6a42'],
        'cancelled'        => ['Đã huỷ',               '#ffe0e0', '#a63652'],
        'return_requested' => ['Đang xử lý hoàn tiền', '#fff0d9', '#b15e1f'],
        'returned'         => ['Đã hoàn tiền',         '#e4dcf5', '#5b4ba5'],
    ];
    $payMap = [
        'cod'  => 'COD',
        'bank' => 'Chuyển khoản',
        'momo' => 'MoMo',
    ];

    $emptyConfig = [
        'active'    => ['title' => 'Chưa có đơn nào',            'desc' => 'Tìm sản phẩm yêu thích và đặt đơn đầu tiên của bạn.'],
        'completed' => ['title' => 'Chưa có đơn hoàn tất',        'desc' => 'Khi bạn xác nhận đã nhận hàng, đơn sẽ xuất hiện ở đây.'],
        'cancelled' => ['title' => 'Không có đơn đã huỷ',         'desc' => 'Tất cả đơn của bạn đang được xử lý hoặc đã hoàn tất.'],
        'returned'  => ['title' => 'Không có đơn trả hàng',       'desc' => 'Bạn chưa từng gửi yêu cầu trả hàng & hoàn tiền nào.'],
    ];
    $empty = $emptyConfig[$activeTab] ?? $emptyConfig['active'];

    $sidebarMap = [
        'active'    => 'orders',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        'returned'  => 'returned',
    ];
    $sidebarActive = $sidebarMap[$activeTab] ?? 'orders';
@endphp

@section('content')
<section class="acc-page">
    <div class="acc-page__inner">
        <div class="acc-page__head">
            <span class="section-chip">Tài khoản</span>
            <h1 class="acc-page__title">{{ $pageTitle }}</h1>
            <p class="acc-page__sub">
                @if(count($orders) > 0)
                    Bạn có {{ count($orders) }} đơn trong mục này.
                @else
                    {{ $empty['desc'] }}
                @endif
            </p>
        </div>

        <div class="acc-layout">
            @include('user.account._sidebar', ['active' => $sidebarActive])

            <div class="acc-content">
                @if(session('cart_flash'))
                    <div class="cart-alert cart-alert--success" style="margin-bottom:18px">{{ session('cart_flash') }}</div>
                @endif

                <nav class="order-tabs" aria-label="Lọc đơn hàng">
                    <a href="{{ route('user.orders') }}"
                       class="order-tab @if($activeTab === 'active') is-active @endif">
                        Đang xử lý
                    </a>
                    <a href="{{ route('user.orders.completed') }}"
                       class="order-tab @if($activeTab === 'completed') is-active @endif">
                        Hoàn tất
                    </a>
                    <a href="{{ route('user.orders.cancelled') }}"
                       class="order-tab @if($activeTab === 'cancelled') is-active @endif">
                        Đã huỷ
                    </a>
                    <a href="{{ route('user.orders.returned') }}"
                       class="order-tab @if($activeTab === 'returned') is-active @endif">
                        Trả hàng / Hoàn tiền
                    </a>
                </nav>

                @if(count($orders) === 0)
                    <div class="cart-empty">
                        <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                            <circle cx="60" cy="60" r="52" fill="#fde4ee"/>
                            <path d="M42 44h36v40H42z" stroke="#d97b9d" stroke-width="3" fill="none"/>
                            <path d="M42 44l-4 10M78 44l4 10M54 58h12" stroke="#d97b9d" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                        <h3>{{ $empty['title'] }}</h3>
                        <p>{{ $empty['desc'] }}</p>
                        <a href="/shop" class="cart-btn cart-btn--primary">Khám phá shop</a>
                    </div>
                @else
                    <div class="order-list">
                        @foreach($orders as $order)
                            @php
                                $rawStatus = $order['status'] ?? '';
                                // Ưu tiên trạng thái thủ công, còn lại tính theo timeline
                                $stageKey = \in_array($rawStatus, ['cancelled', 'returned', 'return_requested', 'received'], true)
                                    ? $rawStatus
                                    : OrderTimeline::currentKey($order);
                                [$statusText, $statusBg, $statusColor] = $statusMap[$stageKey] ?? $statusMap['pending'];
                                $payText = $payMap[$order['payment']] ?? $order['payment'];
                                $totalQty = array_sum(array_column($order['items'], 'qty'));
                            @endphp
                            <article class="order-card">
                                <header class="order-card__head">
                                    <div>
                                        <span class="order-card__id">#{{ $order['id'] }}</span>
                                        <span class="order-card__date">{{ \Carbon\Carbon::parse($order['created_at'])->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <span class="order-card__status" style="background:{{ $statusBg }};color:{{ $statusColor }}">
                                        {{ $statusText }}
                                    </span>
                                </header>

                                <div class="order-card__body">
                                    <div class="order-card__thumbs">
                                        @foreach(array_slice($order['items'], 0, 4) as $item)
                                            <div class="order-card__thumb">
                                                <img src="{{ $item['image'] ?? '/images/1.jpg' }}" alt="{{ $item['name'] }}">
                                            </div>
                                        @endforeach
                                        @if(count($order['items']) > 4)
                                            <div class="order-card__thumb order-card__thumb--more">
                                                +{{ count($order['items']) - 4 }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="order-card__names">
                                        @foreach(array_slice($order['items'], 0, 2) as $item)
                                            <div class="order-card__name-row">
                                                <span class="order-card__name">{{ $item['name'] }}</span>
                                                <span class="order-card__qty">× {{ $item['qty'] }}</span>
                                            </div>
                                        @endforeach
                                        @if(count($order['items']) > 2)
                                            <span class="order-card__more-line">+{{ count($order['items']) - 2 }} sản phẩm khác</span>
                                        @endif
                                    </div>
                                </div>

                                @if($activeTab === 'cancelled' && !empty($order['cancel_reason']))
                                    <div class="order-card__reason">
                                        <strong>Lý do huỷ:</strong> {{ $order['cancel_reason'] }}
                                    </div>
                                @elseif($activeTab === 'returned' && !empty($order['return_reason']))
                                    <div class="order-card__reason">
                                        <strong>Lý do trả hàng & hoàn tiền:</strong> {{ $order['return_reason'] }}
                                    </div>
                                @elseif($activeTab === 'completed' && !empty($order['received_at']))
                                    <div class="order-card__reason order-card__reason--success">
                                        ✓ Đã xác nhận nhận hàng vào {{ \Carbon\Carbon::parse($order['received_at'])->format('H:i · d/m/Y') }}
                                    </div>
                                @endif

                                <footer class="order-card__foot">
                                    <div class="order-card__meta">
                                        <span>{{ $totalQty }} sản phẩm · {{ $payText }}</span>
                                    </div>
                                    <div class="order-card__right">
                                        <span class="order-card__total">{{ number_format($order['total'], 0, ',', '.') }} ₫</span>
                                        <a href="{{ route('user.orders.show', ['id' => $order['id']]) }}" class="cart-btn cart-btn--ghost">Xem chi tiết</a>
                                    </div>
                                </footer>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
