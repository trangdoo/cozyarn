@extends('layouts.admin')

@section('title', 'Dashboard — Quản trị CozyYarn')
@section('page_title', 'Dashboard')

@php $active = 'dashboard'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Xin chào, {{ auth()->user()->name }} ✨</h1>
            <p>Tổng quan hoạt động shop hôm nay {{ now()->translatedFormat('l, d/m/Y') }}</p>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="admin-stats">
        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--pink">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Tài khoản</small>
                <strong>{{ $stats['users'] }}</strong>
                <span class="admin-stat__trend admin-stat__trend--up">↑ hoạt động</span>
            </div>
        </div>

        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 8l8-4 8 4v8l-8 4-8-4V8zM4 8l8 4 8-4M12 12v8"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Sản phẩm</small>
                <strong>{{ $stats['products'] }}</strong>
                <span class="admin-stat__trend">đang bán</span>
            </div>
        </div>

        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Đơn hàng</small>
                <strong>{{ $stats['orders'] }}</strong>
                <span class="admin-stat__trend">trong phiên</span>
            </div>
        </div>

        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M6 6h9a3 3 0 0 1 0 6h-6a3 3 0 0 0 0 6h9"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Doanh thu (demo)</small>
                <strong>{{ number_format($stats['revenue'], 0, ',', '.') }} ₫</strong>
                <span class="admin-stat__trend admin-stat__trend--up">↑ 12% tuần này</span>
            </div>
        </div>
    </div>

    <div class="admin-grid-2">
        {{-- Recent orders --}}
        <section class="admin-card">
            <header class="admin-card__head">
                <h2>Đơn hàng gần đây</h2>
                <a href="{{ route('admin.orders.index') }}">Xem tất cả →</a>
            </header>
            @if(count($recentOrders) === 0)
                <div class="admin-empty">
                    <p>Chưa có đơn nào trong phiên hiện tại.</p>
                </div>
            @else
                <table class="admin-table">
                    <thead>
                        <tr><th>Mã đơn</th><th>Khách</th><th>Tổng</th><th>Trạng thái</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', ['id' => $order['id']]) }}">#{{ $order['id'] }}</a></td>
                                <td>{{ $order['name'] ?? '—' }}</td>
                                <td>{{ number_format($order['total'] ?? 0, 0, ',', '.') }} ₫</td>
                                <td>
                                    @php $s = $order['status'] ?? 'pending'; @endphp
                                    <span class="admin-badge admin-badge--{{ $s }}">{{ $s }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        {{-- Recent users --}}
        <section class="admin-card">
            <header class="admin-card__head">
                <h2>Người dùng mới</h2>
                <a href="{{ route('admin.users.index') }}">Xem tất cả →</a>
            </header>
            <ul class="admin-user-list">
                @forelse($recentUsers as $u)
                    <li>
                        <div class="admin-user-list__avatar">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                        <div class="admin-user-list__body">
                            <strong>{{ $u->name }}</strong>
                            <small>{{ $u->email }}</small>
                        </div>
                        <span class="admin-badge admin-badge--{{ $u->role }}">{{ $u->role }}</span>
                    </li>
                @empty
                    <li class="admin-empty"><p>Chưa có tài khoản nào.</p></li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Quick links --}}
    <section class="admin-quick">
        <h2>Thao tác nhanh</h2>
        <div class="admin-quick__grid">
            <a href="{{ route('admin.products.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#fde4ee;color:#b55a82">＋</span>
                <strong>Thêm sản phẩm</strong>
                <small>Tạo sản phẩm mới cho shop</small>
            </a>
            <a href="{{ route('admin.blog.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#e0f0ff;color:#2c5580">✎</span>
                <strong>Viết bài blog</strong>
                <small>Chia sẻ kiến thức cho khách</small>
            </a>
            <a href="{{ route('admin.notifications.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#fff0e3;color:#b15e1f">🔔</span>
                <strong>Gửi thông báo</strong>
                <small>Push khuyến mãi tới khách hàng</small>
            </a>
            <a href="{{ route('admin.categories.create') }}" class="admin-quick__card">
                <span class="admin-quick__icon" style="background:#e4dcf5;color:#5b4ba5">⊞</span>
                <strong>Thêm danh mục</strong>
                <small>Phân loại sản phẩm</small>
            </a>
        </div>
    </section>
</div>
@endsection
