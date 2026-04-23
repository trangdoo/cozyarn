@extends('layouts.admin')

@section('title', 'Dashboard — Quản trị CozyYarn')
@section('page_title', 'Dashboard')

@php
    $active = 'dashboard';

    // Chuyển array giá trị → array point [x,y] (view box 0-100)
    $sparkPoints = function (array $values): array {
        if (count($values) < 2) return [];
        $min = min($values); $max = max($values);
        if ($max == $min) { $min = 0; $max = max(1, $max); }
        $n = count($values);
        $points = [];
        foreach ($values as $i => $v) {
            $x = round($i / ($n - 1) * 100, 2);
            $y = round(100 - ($v - $min) / ($max - $min) * 70 - 15, 2);
            $points[] = [$x, $y];
        }
        return $points;
    };
    // Smooth Bezier curve giữa các điểm (cardinal spline)
    $sparkPath = function (array $values) use ($sparkPoints): string {
        $p = $sparkPoints($values);
        if (\count($p) < 2) return '';
        $path = "M{$p[0][0]},{$p[0][1]}";
        for ($i = 1; $i < \count($p); $i++) {
            $a = $p[$i - 1]; $b = $p[$i];
            $cp1x = $a[0] + ($b[0] - $a[0]) * 0.5;
            $cp1y = $a[1];
            $cp2x = $a[0] + ($b[0] - $a[0]) * 0.5;
            $cp2y = $b[1];
            $path .= " C{$cp1x},{$cp1y} {$cp2x},{$cp2y} {$b[0]},{$b[1]}";
        }
        return $path;
    };
    $sparkArea = function (array $values) use ($sparkPath): string {
        $path = $sparkPath($values);
        return $path ? "{$path} L100,100 L0,100 Z" : '';
    };
    $sparkLast = function (array $values) use ($sparkPoints): array {
        $p = $sparkPoints($values);
        return \count($p) > 0 ? end($p) : [100, 50];
    };

    $fmtVnd = fn(int $n) => number_format($n, 0, ',', '.') . '₫';
    $compareLabel = $isToday ? 'Hôm qua' : 'Ngày trước';

    $compareText = $isToday ? 'vs hôm qua' : 'vs ngày trước đó';
@endphp

@section('content')
<script src="/vendor/chartjs/chart.umd.min.js"></script>
<div class="admin-page admin-dash">

    {{-- ═══════════════════ ROW 1: HERO ═══════════════════ --}}
    <div class="admin-dash__hero">
        <div>
            <h1>Chào {{ auth()->user()->name }} <span style="font-size:0.85em">♡</span></h1>
            <p>Thống kê ngày · <strong>{{ $today['date'] }}</strong></p>
        </div>
        @php
            $prevDay = \Carbon\Carbon::parse($selectedDay)->copy()->subDay()->toDateString();
            $nextDay = \Carbon\Carbon::parse($selectedDay)->copy()->addDay();
            $canNextDay = $nextDay->lte(now());
        @endphp
        <div class="dash-chart__range" data-day-picker>
            <button type="button" class="admin-dash__today-pill" data-day-toggle>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/><circle cx="12" cy="14" r="2" fill="currentColor"/></svg>
                <span>{{ $isToday ? 'Hôm nay' : $today['shortDate'] }}</span>
                <svg class="dash-chart__pill-caret" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5l3 3 3-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="dash-chart__menu" data-day-menu hidden>
                <div class="dash-chart__menu-title">Chọn ngày bất kỳ</div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="dash-chart__date-form">
                    <input type="hidden" name="range" value="{{ $range }}">
                    <input type="hidden" name="end" value="{{ $endDate }}">
                    <input type="date" name="day" value="{{ $selectedDay }}" max="{{ now()->toDateString() }}" required>
                    <button type="submit" class="dash-chart__menu-apply">Xem</button>
                </form>
                <div class="dash-chart__menu-divider"></div>
                <div class="dash-chart__menu-nav">
                    <a href="{{ route('admin.dashboard', ['day' => $prevDay, 'range' => $range, 'end' => $endDate]) }}" class="dash-chart__menu-nav-btn">← Ngày trước</a>
                    <a href="{{ route('admin.dashboard', ['day' => now()->toDateString(), 'range' => $range, 'end' => $endDate]) }}" class="dash-chart__menu-nav-btn dash-chart__menu-nav-btn--now">Hôm nay</a>
                    @if($canNextDay)
                        <a href="{{ route('admin.dashboard', ['day' => $nextDay->toDateString(), 'range' => $range, 'end' => $endDate]) }}" class="dash-chart__menu-nav-btn">Ngày sau →</a>
                    @else
                        <span class="dash-chart__menu-nav-btn is-disabled">Ngày sau →</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ ROW 2: 4 KPI ═══════════════════ --}}
    <div class="admin-dash__stats">
        @php
            $kpiCards = [
                [
                    'tone'  => 'pink',
                    'icon'  => '<path d="M12 2v20M6 6h9a3 3 0 0 1 0 6h-6a3 3 0 0 0 0 6h9"/>',
                    'label' => 'Doanh thu',
                    'value' => $fmtVnd((int) $today['revenue']),
                ],
                [
                    'tone'  => 'blue',
                    'icon'  => '<path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
                    'label' => 'Đơn hàng',
                    'value' => (string) $today['orders'],
                ],
                [
                    'tone'  => 'orange',
                    'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M9 12h6M12 9v6" stroke-linecap="round"/>',
                    'label' => 'Giá trị TB/đơn',
                    'value' => $fmtVnd((int) $today['aov']),
                ],
                [
                    'tone'  => 'green',
                    'icon'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/>',
                    'label' => 'Khách mới',
                    'value' => (string) $today['users'],
                ],
            ];
        @endphp

        @foreach($kpiCards as $c)
            <div class="kpi-card kpi-card--{{ $c['tone'] }}">
                <header class="kpi-card__head">
                    <span class="kpi-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $c['icon'] !!}</svg>
                    </span>
                    <span class="kpi-card__label">{{ $c['label'] }}</span>
                </header>
                <div class="kpi-card__value">{{ $c['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ═══════════════════ ROW 3: CHART + ACTION QUEUE ═══════════════════ --}}
    <div class="dash-grid dash-grid--chart-queue">
        <section class="dash-card dash-card--chart">
            <header class="dash-card__head">
                <div>
                    <h2>Doanh thu & Đơn hàng</h2>
                    <small>Biểu đồ xu hướng {{ $rangeLabel }}</small>
                </div>
                <div class="dash-chart__head-right">
                    <a href="{{ route('admin.orders.export', ['format' => 'csv']) }}" class="dash-chart__export-btn" title="Xuất báo cáo đơn hàng CSV">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v14M6 11l6 6 6-6M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Xuất báo cáo
                    </a>
                    <div class="dash-chart__range" data-range-picker>
                        <button type="button" class="dash-chart__pill" data-range-toggle>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
                            <span>{{ $rangeLabel }} · {{ \Carbon\Carbon::parse($endDate)->format('d/m') }}</span>
                            <svg class="dash-chart__pill-caret" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5l3 3 3-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="dash-chart__menu" data-range-menu hidden>
                            <div class="dash-chart__menu-title">Tổng hợp theo</div>
                            <a href="{{ route('admin.dashboard', ['range' => '7d', 'end' => $endDate, 'day' => $selectedDay]) }}" class="dash-chart__menu-item @if($range === '7d') is-active @endif">Tuần</a>
                            <a href="{{ route('admin.dashboard', ['range' => '12m', 'end' => $endDate, 'day' => $selectedDay]) }}" class="dash-chart__menu-item @if($range === '12m') is-active @endif">Tháng</a>
                            <a href="{{ route('admin.dashboard', ['range' => '5y',  'end' => $endDate, 'day' => $selectedDay]) }}" class="dash-chart__menu-item @if($range === '5y') is-active @endif">Năm</a>
                            <div class="dash-chart__menu-divider"></div>
                            <div class="dash-chart__menu-title">Xem đến ngày</div>
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="dash-chart__date-form">
                                <input type="hidden" name="range" value="{{ $range }}">
                                <input type="hidden" name="day" value="{{ $selectedDay }}">
                                <input type="date" name="end" value="{{ $endDate }}" max="{{ now()->toDateString() }}" required>
                                <button type="submit" class="dash-chart__menu-apply">Áp dụng</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <div class="dash-chart__tabs" role="tablist">
                <button type="button" class="dash-chart__tab is-active" data-chart-tab="revenue" role="tab" aria-selected="true">
                    <span class="dash-chart__tab-dot" style="background:#b55a82"></span>
                    <div class="dash-chart__tab-body">
                        <small>Doanh thu {{ $rangeLabel }}</small>
                        <strong>{{ number_format(array_sum($chart['revenue']), 0, ',', '.') }}₫</strong>
                    </div>
                </button>
                <button type="button" class="dash-chart__tab" data-chart-tab="orders" role="tab" aria-selected="false">
                    <span class="dash-chart__tab-dot" style="background:#7c6cdc"></span>
                    <div class="dash-chart__tab-body">
                        <small>Đơn hàng {{ $rangeLabel }}</small>
                        <strong>{{ array_sum($chart['orders']) }} đơn</strong>
                    </div>
                </button>
            </div>
            <div class="dash-chart__wrap">
                <div class="dash-chart__canvas-box">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </section>

        {{-- Action Queue --}}
        <section class="dash-card dash-queue">
            <header class="dash-card__head">
                <div><h2>Cần xử lý</h2><small>Các việc đang chờ bạn</small></div>
            </header>
            <ul class="dash-queue__list">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="dash-queue__item @if($actionQueue['pending'] > 0) is-active @endif">
                    <span class="dash-queue__icon" style="background:#fff6cc;color:#9a7a1f">📋</span>
                    <div class="dash-queue__body">
                        <strong>Đơn chờ xác nhận</strong>
                        <small>Cần xác nhận để chuyển sang đóng gói</small>
                    </div>
                    <span class="dash-queue__count">{{ $actionQueue['pending'] }}</span>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="dash-queue__item @if($actionQueue['shipping'] > 0) is-active @endif">
                    <span class="dash-queue__icon" style="background:#e0f0ff;color:#2c5580">📦</span>
                    <div class="dash-queue__body">
                        <strong>Sẵn sàng giao</strong>
                        <small>Đã xác nhận, chờ bàn giao vận chuyển</small>
                    </div>
                    <span class="dash-queue__count">{{ $actionQueue['shipping'] }}</span>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'return_requested']) }}" class="dash-queue__item @if($actionQueue['return_req'] > 0) is-active is-warning @endif">
                    <span class="dash-queue__icon" style="background:#fff0d9;color:#b15e1f">↻</span>
                    <div class="dash-queue__body">
                        <strong>Yêu cầu trả hàng</strong>
                        <small>Khách gửi yêu cầu hoàn tiền</small>
                    </div>
                    <span class="dash-queue__count">{{ $actionQueue['return_req'] }}</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="dash-queue__item">
                    <span class="dash-queue__icon" style="background:#fde4ee;color:#b55a82">💬</span>
                    <div class="dash-queue__body">
                        <strong>Đánh giá chờ phản hồi</strong>
                        <small>Khách đã để lại review</small>
                    </div>
                    <span class="dash-queue__count">{{ $actionQueue['review_noreply'] }}</span>
                </a>
                <a href="{{ route('admin.products.index', ['sort' => 'stock_asc']) }}" class="dash-queue__item @if($actionQueue['low_stock'] > 0) is-active is-danger @endif">
                    <span class="dash-queue__icon" style="background:#ffe0e0;color:#a63652">⚠</span>
                    <div class="dash-queue__body">
                        <strong>Sắp hết hàng</strong>
                        <small>Tồn kho &lt; 15 sản phẩm</small>
                    </div>
                    <span class="dash-queue__count">{{ $actionQueue['low_stock'] }}</span>
                </a>
            </ul>
        </section>
    </div>

    {{-- ═══════════════════ ROW 4: CATEGORY / PAYMENT / STATUS ═══════════════════ --}}
    <div class="dash-grid dash-grid--3">
        {{-- Sales by category --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>Doanh thu theo danh mục</h2><small>Luỹ kế từ đầu</small></div>
            </header>
            <ul class="dash-bar-list">
                @forelse($categorySales as $slug => $cat)
                    @php $pct = $maxCatRevenue > 0 ? round($cat['revenue'] / $maxCatRevenue * 100) : 0; @endphp
                    <li>
                        <div class="dash-bar-list__head">
                            <strong>{{ $cat['name'] }}</strong>
                            <span>{{ number_format($cat['revenue'], 0, ',', '.') }}₫</span>
                        </div>
                        <div class="dash-bar-list__bar"><i style="width: {{ $pct }}%"></i></div>
                        <small>{{ $cat['qty'] }} sản phẩm đã bán</small>
                    </li>
                @empty
                    <li class="admin-empty"><p>Chưa có doanh thu.</p></li>
                @endforelse
            </ul>
        </section>

        {{-- Payment methods --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>Phương thức thanh toán</h2><small>{{ $totalPaidOrders }} đơn</small></div>
            </header>
            <ul class="dash-payment-list">
                @foreach($paymentStats as $key => $pm)
                    @php $pct = $totalPaidOrders > 0 ? round($pm['count'] / $totalPaidOrders * 100) : 0; @endphp
                    <li class="dash-payment-item dash-payment-item--{{ $key }}">
                        <div class="dash-payment-item__head">
                            <strong>{{ $pm['label'] }}</strong>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="dash-payment-item__bar"><i style="width: {{ $pct }}%"></i></div>
                        <div class="dash-payment-item__meta">
                            <small>{{ $pm['count'] }} đơn</small>
                            <strong>{{ number_format($pm['revenue'], 0, ',', '.') }}₫</strong>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Status donut --}}
        <section class="dash-card dash-card--donut">
            <header class="dash-card__head">
                <div><h2>Trạng thái đơn</h2><small>Toàn hệ thống</small></div>
            </header>
            @php
                $totalStatus = $statusDist['pending'] + $statusDist['confirmed'] + $statusDist['shipping']
                    + $statusDist['delivered'] + $statusDist['received'] + $statusDist['cancelled']
                    + $statusDist['return_req'] + $statusDist['returned'];
            @endphp
            <div class="dash-donut">
                <div class="dash-donut__canvas-wrap">
                    <canvas id="statusChart"></canvas>
                    <div class="dash-donut__center">
                        <strong>{{ $totalStatus }}</strong>
                        <small>tổng đơn</small>
                    </div>
                </div>
            </div>
            <ul class="dash-donut__legend">
                <li><a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"><i style="background:#f3d88a"></i>Chờ xác nhận <strong>{{ $statusDist['pending'] }}</strong></a></li>
                <li><a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}"><i style="background:#b5c9f0"></i>Chờ lấy hàng <strong>{{ $statusDist['confirmed'] }}</strong></a></li>
                <li><a href="{{ route('admin.orders.index', ['status' => 'shipping']) }}"><i style="background:#f8bcd5"></i>Đang giao <strong>{{ $statusDist['shipping'] }}</strong></a></li>
                <li><a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}"><i style="background:#c4e8b7"></i>Đã giao <strong>{{ $statusDist['delivered'] + $statusDist['received'] }}</strong></a></li>
                <li><a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"><i style="background:#f5b8a8"></i>Đã huỷ <strong>{{ $statusDist['cancelled'] }}</strong></a></li>
                <li><a href="{{ route('admin.orders.index', ['status' => 'return_requested']) }}"><i style="background:#d5b8eb"></i>Trả/hoàn <strong>{{ $statusDist['return_req'] + $statusDist['returned'] }}</strong></a></li>
            </ul>
        </section>
    </div>

    {{-- ═══════════════════ ROW 5: TOP + RECENT ORDERS ═══════════════════ --}}
    <div class="dash-grid">
        {{-- Top products --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>Bán chạy</h2><small>Top 5 luỹ kế</small></div>
                <a href="{{ route('admin.products.index') }}" class="dash-card__link">Tất cả →</a>
            </header>
            @if(count($topProducts) === 0)
                <div class="admin-empty"><p>Chưa có dữ liệu.</p></div>
            @else
                <ul class="dash-top-list">
                    @foreach($topProducts as $i => $p)
                        <li>
                            <span class="dash-top-list__rank dash-top-list__rank--{{ $i + 1 }}">#{{ $i + 1 }}</span>
                            <div class="dash-top-list__thumb"><img src="{{ $p['image'] }}" alt=""></div>
                            <div class="dash-top-list__body">
                                <strong>{{ $p['name'] }}</strong>
                                <small>{{ number_format($p['revenue'], 0, ',', '.') }}₫</small>
                            </div>
                            <span class="dash-top-list__qty">{{ $p['qty'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Recent orders --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>Đơn gần đây</h2><small>{{ count($recentOrders) }} đơn mới</small></div>
                <a href="{{ route('admin.orders.index') }}" class="dash-card__link">Tất cả →</a>
            </header>
            @if(count($recentOrders) === 0)
                <div class="admin-empty"><p>Chưa có đơn.</p></div>
            @else
                <ul class="dash-order-list">
                    @foreach($recentOrders as $o)
                        @php
                            $s = $o['status'] ?? 'pending';
                            $stageInfo = [
                                'pending'   => ['Chờ',       '#fff6cc', '#9a7a1f'],
                                'confirmed' => ['Chờ lấy',   '#e0f0ff', '#2c5580'],
                                'shipping'  => ['Đang giao', '#fde4ee', '#b55a82'],
                                'delivered' => ['Đã giao',   '#c3e8d5', '#3d7a52'],
                                'received'  => ['Hoàn tất',  '#d4efdb', '#2f6a42'],
                                'cancelled' => ['Đã huỷ',    '#ffe0e0', '#a63652'],
                            ];
                            [$text, $bg, $color] = $stageInfo[$s] ?? $stageInfo['pending'];
                        @endphp
                        <li>
                            <a href="{{ route('admin.orders.show', $o['id']) }}" class="dash-order-list__link">
                                <div class="dash-order-list__main">
                                    <strong>#{{ $o['id'] }}</strong>
                                    <small>{{ $o['name'] ?? '—' }} · {{ \Carbon\Carbon::parse($o['created_at'])->diffForHumans() }}</small>
                                </div>
                                <div class="dash-order-list__right">
                                    <strong>{{ number_format($o['total'] ?? 0, 0, ',', '.') }}₫</strong>
                                    <span style="background:{{ $bg }};color:{{ $color }};padding:2px 8px;border-radius:8px;font-size:10.5px;font-weight:700">{{ $text }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- ═══════════════════ ROW 6: Activity feeds — Tin nhắn / Thông báo / Đánh giá ═══════════════════ --}}
    <div class="dash-grid dash-grid--3">
        {{-- Tin nhắn gần đây --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>💬 Tin nhắn gần đây</h2><small>{{ count($recentChats) }} hội thoại</small></div>
                <a href="{{ route('admin.chat.index') }}" class="dash-card__link">Tất cả →</a>
            </header>
            @if(count($recentChats) === 0)
                <div class="admin-empty"><p>Chưa có hội thoại nào.</p></div>
            @else
                <ul class="dash-feed-list">
                    @foreach($recentChats as $t)
                        @php
                            $lastMsg = !empty($t['messages'] ?? []) ? end($t['messages']) : null;
                            $preview = $t['last_preview'] ?? ($lastMsg['content'] ?? $t['subtitle'] ?? '');
                        @endphp
                        <li>
                            <a href="{{ route('admin.chat.show', $t['id']) }}" class="dash-feed-list__link">
                                <div class="dash-feed-list__avatar">
                                    @if(!empty($t['product']['image']))
                                        <img src="{{ $t['product']['image'] }}" alt="">
                                    @else
                                        <span>{{ mb_strtoupper(mb_substr($t['title'] ?? 'U', 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="dash-feed-list__body">
                                    <div class="dash-feed-list__head">
                                        <strong>{{ $t['title'] ?? '—' }}</strong>
                                        <small>{{ \Carbon\Carbon::parse($t['updated_at'] ?? now())->diffForHumans(null, true) }}</small>
                                    </div>
                                    <p>{{ \Illuminate\Support\Str::limit($preview, 60) }}</p>
                                </div>
                                @if(($t['unread'] ?? 0) > 0)
                                    <span class="dash-feed-list__unread">{{ $t['unread'] > 9 ? '9+' : $t['unread'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Thông báo gần đây --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>🔔 Thông báo gần đây</h2><small>Cập nhật mới nhất</small></div>
                <a href="{{ route('admin.notifications.index') }}" class="dash-card__link">Tất cả →</a>
            </header>
            @if(count($recentNotifications) === 0)
                <div class="admin-empty"><p>Chưa có thông báo.</p></div>
            @else
                <ul class="dash-feed-list">
                    @foreach($recentNotifications as $n)
                        @php
                            $iconMap = [
                                'promo-discount'  => ['#fff0e3', '#b15e1f', '🎁'],
                                'promo-ship'      => ['#fde4ee', '#b55a82', '🚚'],
                                'promo-new'       => ['#e0f0ff', '#2c5580', '✨'],
                                'order-placed'    => ['#fff6cc', '#9a7a1f', '📋'],
                                'order-confirmed' => ['#c3e8d5', '#3d7a52', '✓'],
                                'order-shipping'  => ['#fde4ee', '#b55a82', '🚛'],
                                'order-delivered' => ['#d4efdb', '#2f6a42', '📦'],
                            ];
                            [$bg, $color, $ico] = $iconMap[$n['icon'] ?? 'promo-new'] ?? ['#fde4ee', '#b55a82', '🔔'];
                        @endphp
                        <li>
                            <a href="{{ $n['link'] ?? route('admin.notifications.index') }}" class="dash-feed-list__link @if(empty($n['is_read'])) is-unread @endif">
                                <div class="dash-feed-list__icon" style="background:{{ $bg }};color:{{ $color }}">{{ $ico }}</div>
                                <div class="dash-feed-list__body">
                                    <div class="dash-feed-list__head">
                                        <strong>{{ $n['title'] ?? '—' }}</strong>
                                        <small>{{ \Carbon\Carbon::parse($n['created_at'] ?? now())->diffForHumans(null, true) }}</small>
                                    </div>
                                    <p>{{ \Illuminate\Support\Str::limit($n['content'] ?? '', 60) }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Đánh giá mới --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>⭐ Đánh giá mới</h2><small>{{ $reviewStats['noreply'] }} chờ phản hồi</small></div>
                <a href="{{ route('admin.orders.index') }}" class="dash-card__link">Tất cả →</a>
            </header>
            @if(count($recentReviewsList) === 0)
                <div class="admin-empty"><p>Chưa có đánh giá nào.</p></div>
            @else
                <ul class="dash-feed-list">
                    @foreach($recentReviewsList as $r)
                        <li>
                            <a href="{{ $r['_order_id'] ? route('admin.orders.show', $r['_order_id']) : '#' }}" class="dash-feed-list__link">
                                <div class="dash-feed-list__avatar">
                                    <img src="{{ $r['_product_image'] }}" alt="">
                                </div>
                                <div class="dash-feed-list__body">
                                    <div class="dash-feed-list__head">
                                        <strong>{{ $r['_user_name'] }}</strong>
                                        <small>{{ \Carbon\Carbon::parse($r['created_at'] ?? now())->diffForHumans(null, true) }}</small>
                                    </div>
                                    <div class="dash-feed-list__stars">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span class="@if($s <= ($r['rating'] ?? 0)) is-on @endif">★</span>
                                        @endfor
                                        <small>{{ \Illuminate\Support\Str::limit($r['_product_name'], 30) }}</small>
                                    </div>
                                    @if(!empty($r['comment']))
                                        <p>{{ \Illuminate\Support\Str::limit($r['comment'], 70) }}</p>
                                    @endif
                                    @if(empty($r['admin_reply']))
                                        <span class="dash-feed-list__reply-badge">Chưa phản hồi →</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- ═══════════════════ ROW 7: Customer insights + Low stock ═══════════════════ --}}
    <div class="dash-grid">

        {{-- Phân tích khách hàng --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>Khách hàng</h2><small>Tổng {{ $customerInsights['total'] }} tài khoản</small></div>
                <a href="{{ route('admin.users.index') }}" class="dash-card__link">Tất cả →</a>
            </header>
            <div class="dash-cust">
                <div class="dash-cust__stats">
                    <div class="dash-cust__stat">
                        <small>Mới trong ngày</small>
                        <strong>{{ $customerInsights['new_today'] }}</strong>
                    </div>
                    <div class="dash-cust__stat">
                        <small>Khách quay lại</small>
                        <strong>{{ $customerInsights['returning'] }}</strong>
                    </div>
                    <div class="dash-cust__stat">
                        <small>Tổng tài khoản</small>
                        <strong>{{ $customerInsights['total'] }}</strong>
                    </div>
                </div>
                @if(count($customerInsights['vip']) > 0)
                    <div class="dash-cust__vip-title">👑 Khách VIP (top chi tiêu)</div>
                    <ul class="dash-cust__vip">
                        @foreach($customerInsights['vip'] as $i => $v)
                            <li>
                                <span class="dash-cust__vip-rank">#{{ $i + 1 }}</span>
                                <div class="admin-user-list__avatar" style="width:32px;height:32px;font-size:12px">{{ mb_strtoupper(mb_substr($v['user']->name, 0, 1)) }}</div>
                                <div class="dash-cust__vip-body">
                                    <strong>{{ $v['user']->name }}</strong>
                                    <small>{{ $v['orders'] }} đơn</small>
                                </div>
                                <span class="dash-cust__vip-spent">{{ number_format($v['spent'], 0, ',', '.') }}₫</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="admin-empty" style="padding:18px"><p>Chưa có khách VIP.</p></div>
                @endif
            </div>
        </section>

        {{-- Sắp hết hàng --}}
        <section class="dash-card">
            <header class="dash-card__head">
                <div><h2>⚠ Sắp hết hàng</h2><small>Tồn kho &lt; 15</small></div>
                <a href="{{ route('admin.products.index', ['sort' => 'stock_asc']) }}" class="dash-card__link">Tất cả →</a>
            </header>
            @if(count($lowStockItems) === 0)
                <div class="admin-empty"><p>✓ Tồn kho ổn định.</p></div>
            @else
                <ul class="dash-top-list">
                    @foreach($lowStockItems as $p)
                        <li>
                            <a href="{{ route('admin.products.edit', ['category' => $p['category'], 'slug' => $p['slug']]) }}" class="dash-top-list__link" style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;text-decoration:none;color:inherit">
                                <div class="dash-top-list__thumb"><img src="{{ $p['image'] }}" alt=""></div>
                                <div class="dash-top-list__body">
                                    <strong>{{ $p['name'] }}</strong>
                                    <small>Còn {{ $p['stock'] }} sản phẩm</small>
                                </div>
                                <span class="dash-top-list__qty" style="background:{{ $p['stock'] <= 5 ? '#ffe0e0' : '#fff0d9' }};color:{{ $p['stock'] <= 5 ? '#a63652' : '#b15e1f' }}">{{ $p['stock'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

    </div>

    {{-- ═══════════════════ ROW 7: Quick actions ═══════════════════ --}}
    <section class="admin-quick">
        <h2>Thao tác nhanh</h2>
        <div class="admin-quick__grid">
            <a href="{{ route('admin.products.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#fde4ee;color:#b55a82">＋</span>
                <strong>Thêm sản phẩm</strong><small>Tạo sản phẩm mới</small>
            </a>
            <a href="{{ route('admin.blog.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#e0f0ff;color:#2c5580">✎</span>
                <strong>Viết bài blog</strong><small>Chia sẻ kiến thức</small>
            </a>
            <a href="{{ route('admin.notifications.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#fff0e3;color:#b15e1f">🔔</span>
                <strong>Gửi thông báo</strong><small>Push khuyến mãi</small>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#e4dcf5;color:#5b4ba5">📦</span>
                <strong>Xử lý đơn</strong><small>Xác nhận đơn mới</small>
            </a>
        </div>
    </section>
</div>

<script>
(() => {
    // ═══ Dropdown pickers ═══
    const pickers = [
        { root: document.querySelector('[data-range-picker]'), toggle: '[data-range-toggle]', menu: '[data-range-menu]' },
        { root: document.querySelector('[data-day-picker]'),   toggle: '[data-day-toggle]',   menu: '[data-day-menu]'   },
    ].filter(p => p.root);
    pickers.forEach(p => {
        p.root.querySelector(p.toggle)?.addEventListener('click', (e) => {
            e.stopPropagation();
            pickers.forEach(o => { if (o !== p) { o.root.querySelector(o.menu).hidden = true; o.root.classList.remove('is-open'); } });
            const mn = p.root.querySelector(p.menu);
            mn.hidden = !mn.hidden;
            p.root.classList.toggle('is-open', !mn.hidden);
        });
    });
    document.addEventListener('click', (e) => {
        pickers.forEach(p => { if (!p.root.contains(e.target)) { p.root.querySelector(p.menu).hidden = true; p.root.classList.remove('is-open'); } });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') pickers.forEach(p => { p.root.querySelector(p.menu).hidden = true; p.root.classList.remove('is-open'); });
    });

    const labels    = @json($chart['labels']);
    const revenue   = @json($chart['revenue']);
    const ordersArr = @json($chart['orders']);
    const sd        = @json($statusDist);

    const chartConfigs = {
        revenue: {
            label: 'Doanh thu',
            data: revenue,
            stroke: '#b55a82',
            gradTop:    'rgba(181,90,130,0.42)',
            gradMid:    'rgba(217,123,157,0.18)',
            gradBottom: 'rgba(217,123,157,0.02)',
            tickColor:  '#b55a82',
            gridColor:  '#fce4ee',
            format: (v) => v >= 1e6 ? (v/1e6).toFixed(1) + 'M' : v >= 1e3 ? (v/1e3).toFixed(0) + 'K' : v,
            tooltipFmt: (v) => ' ' + v.toLocaleString('vi-VN') + '₫',
            stepSize: undefined,
        },
        orders: {
            label: 'Đơn hàng',
            data: ordersArr,
            stroke: '#7c6cdc',
            gradTop:    'rgba(124,108,220,0.42)',
            gradMid:    'rgba(124,108,220,0.18)',
            gradBottom: 'rgba(124,108,220,0.02)',
            tickColor:  '#7c6cdc',
            gridColor:  '#ede9fb',
            format: (v) => Number.isInteger(v) ? v : '',
            tooltipFmt: (v) => ' ' + v + ' đơn',
            stepSize: 1,
        },
    };

    let activeKey = 'revenue';

    function initRevenueChart() {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        function buildGradient(cfg) {
            const g = ctx.createLinearGradient(0, 0, 0, 300);
            g.addColorStop(0, cfg.gradTop);
            g.addColorStop(0.6, cfg.gradMid);
            g.addColorStop(1, cfg.gradBottom);
            return g;
        }
        function datasetFor(key) {
            const cfg = chartConfigs[key];
            return {
                label: cfg.label,
                data: cfg.data,
                borderColor: cfg.stroke,
                backgroundColor: buildGradient(cfg),
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: cfg.stroke,
                pointHoverBorderWidth: 3,
            };
        }

        const chart = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [datasetFor('revenue')] },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff', titleColor: '#6d3e55', bodyColor: '#4a3644',
                        borderColor: '#f5d6e3', borderWidth: 1, padding: 12, cornerRadius: 10,
                        callbacks: {
                            label: (c) => chartConfigs[activeKey].tooltipFmt(c.parsed.y)
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false },
                        ticks: { color: '#a6849a', font: { size: 12 }, maxRotation: 0, autoSkip: true } },
                    y: { beginAtZero: true, grid: { color: '#fce4ee', drawTicks: false }, border: { display: false },
                        ticks: { color: '#b55a82', font: { size: 11 }, padding: 8,
                            callback: (v) => chartConfigs[activeKey].format(v) } }
                }
            }
        });

        function setActiveTab(key) {
            activeKey = key;
            const cfg = chartConfigs[key];
            chart.data.datasets = [datasetFor(key)];
            chart.options.scales.y.ticks.color = cfg.tickColor;
            chart.options.scales.y.grid.color  = cfg.gridColor;
            if (cfg.stepSize !== undefined) chart.options.scales.y.ticks.stepSize = cfg.stepSize;
            else delete chart.options.scales.y.ticks.stepSize;
            chart.update();

            document.querySelectorAll('[data-chart-tab]').forEach(t => {
                const active = t.dataset.chartTab === key;
                t.classList.toggle('is-active', active);
                t.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }
        document.querySelectorAll('[data-chart-tab]').forEach(t => {
            t.addEventListener('click', () => setActiveTab(t.dataset.chartTab));
        });
    }

    function initStatusDonut() {
        const canvas = document.getElementById('statusChart');
        if (!canvas) return;
        const donutData = [sd.pending, sd.confirmed, sd.shipping, sd.delivered + sd.received, sd.cancelled, sd.return_req + sd.returned];
        const statusColors = ['#f3d88a', '#b5c9f0', '#f8bcd5', '#c4e8b7', '#f5b8a8', '#d5b8eb'];
        const totalDonut = donutData.reduce((a, b) => a + b, 0);
        new Chart(canvas, {
            type: 'doughnut',
            data: { labels: ['Chờ xác nhận', 'Chờ lấy hàng', 'Đang giao', 'Đã giao', 'Đã huỷ', 'Trả/hoàn'],
                    datasets: [{ data: totalDonut === 0 ? [1] : donutData,
                                 backgroundColor: totalDonut === 0 ? ['#f5d6e3'] : statusColors,
                                 borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: true, cutout: '72%',
                plugins: { legend: { display: false },
                    tooltip: totalDonut === 0 ? { enabled: false } : { backgroundColor: '#fff', titleColor: '#6d3e55', bodyColor: '#4a3644', borderColor: '#f5d6e3', borderWidth: 1, padding: 10, cornerRadius: 10 } } }
        });
    }

    function showChartError(canvasId, message) {
        const c = document.getElementById(canvasId);
        if (!c) return;
        const note = document.createElement('div');
        note.style.cssText = 'padding:16px;color:#a63652;background:#ffe0e0;border-radius:10px;font-size:13px;text-align:center';
        note.textContent = message;
        c.replaceWith(note);
    }

    function bootCharts() {
        if (typeof Chart === 'undefined') {
            const fallback = document.createElement('script');
            fallback.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            fallback.onload = bootCharts;
            fallback.onerror = () => {
                showChartError('revenueChart', '⚠ Không thể tải Chart.js (kiểm tra mạng)');
                showChartError('statusChart',  '⚠ Không thể tải Chart.js');
            };
            document.head.appendChild(fallback);
            return;
        }
        try { initRevenueChart(); } catch (e) {
            console.error('[dashboard] revenue chart error:', e);
            showChartError('revenueChart', '⚠ Lỗi biểu đồ doanh thu: ' + (e.message || e));
        }
        try { initStatusDonut(); } catch (e) {
            console.error('[dashboard] status donut error:', e);
            showChartError('statusChart', '⚠ Lỗi donut: ' + (e.message || e));
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootCharts);
    } else {
        bootCharts();
    }
})();
</script>
@endsection
