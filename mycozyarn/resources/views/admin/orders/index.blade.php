@extends('layouts.admin')

@section('title', 'Đơn hàng — CozyYarn')
@section('page_title', 'Đơn hàng')

@php
    use App\Support\OrderTimeline;
    $active = 'orders';
    $statusMap = [
        'pending' => ['Chờ xác nhận', '#fff6cc', '#9a7a1f'],
        'confirmed' => ['Chờ lấy hàng', '#e0f0ff', '#2c5580'],
        'shipping' => ['Đang giao', '#fde4ee', '#b55a82'],
        'delivered' => ['Đã giao', '#c3e8d5', '#3d7a52'],
        'received' => ['Hoàn tất', '#d4efdb', '#2f6a42'],
        'cancelled' => ['Đã huỷ', '#ffe0e0', '#a63652'],
        'return_requested' => ['Yêu cầu trả', '#fff0d9', '#b15e1f'],
        'returned' => ['Đã trả', '#e4dcf5', '#5b4ba5'],
    ];
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Đơn hàng</h1>
            <p>{{ $stats['all'] }} đơn — lọc theo trạng thái bên dưới</p>
        </div>
    </div>

    <div class="admin-stats admin-stats--compact">
        <div class="admin-stat-sm"><small>Tổng đơn</small><strong>{{ $stats['all'] }}</strong></div>
        <div class="admin-stat-sm"><small>Chờ xử lý</small><strong>{{ $stats['pending'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đang giao</small><strong>{{ $stats['shipping'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đã giao</small><strong>{{ $stats['delivered'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đã huỷ</small><strong>{{ $stats['cancelled'] }}</strong></div>
        <div class="admin-stat-sm"><small>Trả hàng</small><strong>{{ $stats['return'] }}</strong></div>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm mã đơn hoặc tên khách...">
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            @foreach($statusMap as $k => $v)
                <option value="{{ $k }}" @selected($filter['status'] === $k)>{{ $v[0] }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    <div class="admin-card">
        <table class="admin-table admin-table--full">
            <thead>
                <tr><th>Mã đơn</th><th>Khách</th><th>Ngày</th><th>SP</th><th>Tổng</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    @php
                        $stage = \in_array($o['status'] ?? '', ['cancelled', 'returned', 'return_requested', 'received'], true)
                            ? $o['status']
                            : OrderTimeline::currentKey($o);
                        [$text, $bg, $color] = $statusMap[$stage] ?? $statusMap['pending'];
                    @endphp
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $o['id']) }}"><strong>#{{ $o['id'] }}</strong></a></td>
                        <td>
                            <strong>{{ $o['name'] ?? '—' }}</strong><br>
                            <small>{{ $o['phone'] ?? '' }}</small>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y H:i') }}</td>
                        <td>{{ count($o['items'] ?? []) }}</td>
                        <td><strong>{{ number_format($o['total'] ?? 0, 0, ',', '.') }}₫</strong></td>
                        <td><small>{{ strtoupper($o['payment'] ?? '—') }}</small></td>
                        <td><span style="background:{{ $bg }};color:{{ $color }};padding:4px 10px;border-radius:12px;font-size:12px;font-weight:700;white-space:nowrap">{{ $text }}</span></td>
                        <td><a href="{{ route('admin.orders.show', $o['id']) }}" class="admin-btn admin-btn--ghost">Chi tiết</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-empty"><p>Chưa có đơn nào trong phiên này.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
