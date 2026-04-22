@extends('layouts.admin')

@section('title', 'Đơn #' . $order['id'] . ' — CozyYarn')
@section('page_title', 'Chi tiết đơn hàng')

@php $active = 'orders'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="admin-back">← Danh sách</a>
            <h1>Đơn #{{ $order['id'] }}</h1>
            <p>Đặt lúc {{ \Carbon\Carbon::parse($order['created_at'])->format('H:i · d/m/Y') }}</p>
        </div>
    </div>

    <div class="admin-grid-2">
        <section class="admin-card">
            <header class="admin-card__head"><h2>Cập nhật trạng thái</h2></header>
            <form method="POST" action="{{ route('admin.orders.status', $order['id']) }}" class="admin-form">
                @csrf @method('PATCH')
                <label>Trạng thái hiện tại
                    <select name="status">
                        @php
                            $options = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Chờ lấy hàng',
                                'shipping' => 'Đang giao',
                                'delivered' => 'Đã giao',
                                'received' => 'Hoàn tất (user đã nhận)',
                                'cancelled' => 'Đã huỷ',
                                'return_requested' => 'Yêu cầu trả hàng',
                                'returned' => 'Đã hoàn tiền',
                            ];
                            $current = $order['status'] ?? 'pending';
                        @endphp
                        @foreach($options as $k => $v)
                            <option value="{{ $k }}" @selected($current === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="admin-btn admin-btn--primary">Lưu trạng thái</button>
            </form>
        </section>

        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin giao hàng</h2></header>
            <ul class="admin-info">
                <li><span>Người nhận</span><strong>{{ $order['name'] ?? '—' }}</strong></li>
                <li><span>SĐT</span><strong>{{ $order['phone'] ?? '—' }}</strong></li>
                <li><span>Địa chỉ</span><strong>{{ $order['address'] ?? '' }}, {{ $order['district'] ?? '' }}, {{ $order['province'] ?? '' }}</strong></li>
                @if(!empty($order['note']))
                    <li><span>Ghi chú</span><strong>{{ $order['note'] }}</strong></li>
                @endif
                <li><span>Thanh toán</span><strong>{{ strtoupper($order['payment'] ?? '—') }}</strong></li>
                <li><span>Tổng đơn</span><strong style="color:#b55a82">{{ number_format($order['total'] ?? 0, 0, ',', '.') }}₫</strong></li>
            </ul>
        </section>
    </div>

    <section class="admin-card">
        <header class="admin-card__head"><h2>Sản phẩm ({{ count($order['items'] ?? []) }})</h2></header>
        <table class="admin-table admin-table--full">
            <thead><tr><th>SP</th><th>Phân loại</th><th>SL</th><th>Đơn giá</th><th>Tạm tính</th></tr></thead>
            <tbody>
                @foreach($order['items'] ?? [] as $it)
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-user-cell__thumb"><img src="{{ $it['image'] ?? '/images/1.jpg' }}" alt=""></div>
                                <div><strong>{{ $it['name'] }}</strong></div>
                            </div>
                        </td>
                        <td><small>{{ $it['variant'] ?? '' }} {{ !empty($it['size']) ? '· ' . $it['size'] : '' }}</small></td>
                        <td>{{ $it['qty'] }}</td>
                        <td>{{ number_format($it['price'], 0, ',', '.') }}₫</td>
                        <td><strong>{{ number_format($it['price'] * $it['qty'], 0, ',', '.') }}₫</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    @if(!empty($order['return_images']) || !empty($order['return_video']))
        <section class="admin-card">
            <header class="admin-card__head"><h2>Bằng chứng trả hàng</h2></header>
            @if(!empty($order['return_reason']))
                <p style="padding:0 20px"><strong>Lý do:</strong> {{ $order['return_reason'] }}</p>
            @endif
            <div class="admin-evidence-grid">
                @foreach($order['return_images'] ?? [] as $img)
                    <a href="{{ $img }}" target="_blank"><img src="{{ $img }}" alt=""></a>
                @endforeach
                @if(!empty($order['return_video']))
                    <video src="{{ $order['return_video'] }}" controls preload="metadata"></video>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection
