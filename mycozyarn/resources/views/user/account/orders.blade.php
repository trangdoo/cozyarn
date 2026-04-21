@extends('layouts.public')

@section('title', 'Đơn hàng của tôi — CozyYarn')

@php
    use App\Support\OrderTimeline;
    $statusMap = [
        'pending'   => ['Chờ xác nhận',     '#fff6cc', '#9a7a1f'],
        'placed'    => ['Đã đặt hàng',      '#fff6cc', '#9a7a1f'],
        'confirmed' => ['Chờ lấy hàng',     '#e0f0ff', '#2c5580'],
        'shipping'  => ['Chờ giao hàng',    '#fde4ee', '#b55a82'],
        'delivered' => ['Giao thành công',  '#c3e8d5', '#3d7a52'],
        'cancelled' => ['Đã huỷ',           '#ffe0e0', '#a63652'],
    ];
    $payMap = [
        'cod'  => 'COD',
        'bank' => 'Chuyển khoản',
        'momo' => 'MoMo',
    ];
@endphp

@section('content')
<section class="acc-page">
    <div class="acc-page__inner">
        <div class="acc-page__head">
            <span class="section-chip">Tài khoản</span>
            <h1 class="acc-page__title">Đơn hàng của tôi</h1>
            <p class="acc-page__sub">
                @if(count($orders) > 0)
                    Bạn có {{ count($orders) }} đơn hàng trong phiên này.
                @else
                    Chưa có đơn hàng nào — cùng khám phá shop nhé!
                @endif
            </p>
        </div>

        <div class="acc-layout">
            @include('user.account._sidebar', ['active' => 'orders'])

            <div class="acc-content">
                @if(count($orders) === 0)
                    <div class="cart-empty">
                        <svg viewBox="0 0 120 120" fill="none" aria-hidden="true">
                            <circle cx="60" cy="60" r="52" fill="#fde4ee"/>
                            <path d="M42 44h36v40H42z" stroke="#d97b9d" stroke-width="3" fill="none"/>
                            <path d="M42 44l-4 10M78 44l4 10M54 58h12" stroke="#d97b9d" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                        <h3>Chưa có đơn nào</h3>
                        <p>Tìm sản phẩm yêu thích và đặt đơn đầu tiên của bạn.</p>
                        <a href="/shop" class="cart-btn cart-btn--primary">Khám phá shop</a>
                    </div>
                @else
                    <div class="order-list">
                        @foreach($orders as $order)
                            @php
                                $stageKey = OrderTimeline::currentKey($order);
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
