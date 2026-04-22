@extends('layouts.admin')

@section('title', $user->name . ' — Chi tiết tài khoản')
@section('page_title', $user->name)

@php
    use App\Support\OrderTimeline;
    $active = 'users';
    $statusMap = [
        'pending'          => ['Chờ xác nhận',     '#fff6cc', '#9a7a1f'],
        'confirmed'        => ['Chờ lấy hàng',     '#e0f0ff', '#2c5580'],
        'shipping'         => ['Đang giao',        '#fde4ee', '#b55a82'],
        'delivered'        => ['Đã giao',          '#c3e8d5', '#3d7a52'],
        'received'         => ['Hoàn tất',         '#d4efdb', '#2f6a42'],
        'cancelled'        => ['Đã huỷ',           '#ffe0e0', '#a63652'],
        'return_requested' => ['Đang hoàn tiền',   '#fff0d9', '#b15e1f'],
        'returned'         => ['Đã hoàn tiền',     '#e4dcf5', '#5b4ba5'],
    ];
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.users.index') }}" class="admin-back">← Về danh sách</a>
            <h1>{{ $user->name }}</h1>
            <p>Chi tiết tài khoản, lịch sử đơn hàng và đánh giá rủi ro</p>
        </div>
        <div class="admin-page__actions">
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggleBlock', $user) }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn--{{ $user->status === 'active' ? 'warning' : 'success' }}">
                        {{ $user->status === 'active' ? '🔒 Khoá tài khoản' : '🔓 Mở khoá' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ═══ RISK CARD (nổi bật trên cùng nếu có rủi ro) ═══ --}}
    @if($risk >= 50 || $user->status === 'blocked')
        <section class="admin-risk admin-risk--{{ $riskLevel['key'] }}">
            <div class="admin-risk__icon">
                @if($riskLevel['key'] === 'critical')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.3 3.9L2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                @endif
            </div>
            <div class="admin-risk__body">
                <strong>Cảnh báo rủi ro: {{ $riskLevel['label'] }}</strong>
                @if(!empty($riskReasons))
                    <ul>
                        @foreach($riskReasons as $r)
                            <li>{{ $r }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endif

    <div class="admin-grid-2">
        {{-- ═══ PROFILE + EDIT FORM ═══ --}}
        <section class="admin-card">
            <header class="admin-card__head">
                <h2>Hồ sơ tài khoản</h2>
            </header>

            <div class="admin-user-head">
                <div class="admin-user-head__avatar">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="admin-user-head__info">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $user->email }}</small>
                    <div class="admin-user-head__tags">
                        <span class="admin-badge admin-badge--{{ $user->role }}">{{ $user->role }}</span>
                        <span class="admin-badge admin-badge--{{ $user->status }}">{{ $user->status === 'active' ? 'Hoạt động' : 'Đã khoá' }}</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form">
                @csrf @method('PATCH')

                @if($errors->any())
                    <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
                @endif

                <div class="admin-form__row">
                    <label>Họ tên
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </label>
                    <label>Email
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </label>
                </div>
                <div class="admin-form__row">
                    <label>Số điện thoại
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    </label>
                    <label>Vai trò
                        <select name="role" @if($user->id === auth()->id()) disabled @endif>
                            <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        </select>
                    </label>
                    <label>Trạng thái
                        <select name="status">
                            <option value="active" @selected(old('status', $user->status) === 'active')>Hoạt động</option>
                            <option value="blocked" @selected(old('status', $user->status) === 'blocked')>Đã khoá</option>
                        </select>
                    </label>
                </div>
                <label>Địa chỉ
                    <textarea name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                </label>

                @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                @endif

                <div class="admin-form__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Lưu thay đổi</button>
                    @if($user->id !== auth()->id())
                        <button type="submit" form="delete-user-form" class="admin-btn admin-btn--danger"
                                onclick="return confirm('Xoá vĩnh viễn tài khoản này? Hành động không thể hoàn tác.');">
                            Xoá tài khoản
                        </button>
                    @endif
                </div>
            </form>
            @if($user->id !== auth()->id())
                <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:none">
                    @csrf @method('DELETE')
                </form>
            @endif
        </section>

        {{-- ═══ META + RISK GAUGE ═══ --}}
        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin hệ thống</h2></header>

            <div class="admin-risk-gauge admin-risk-gauge--{{ $riskLevel['key'] }}">
                <div class="admin-risk-gauge__top">
                    <small>Điểm rủi ro</small>
                    <strong>{{ $risk }}/100</strong>
                </div>
                <div class="admin-risk-gauge__bar">
                    <i style="width: {{ $risk }}%"></i>
                </div>
                <small class="admin-risk-gauge__label">{{ $riskLevel['label'] }}</small>
            </div>

            <ul class="admin-info">
                <li><span>User ID</span><strong>#{{ $user->id }}</strong></li>
                <li><span>Ngày tạo</span><strong>{{ $user->created_at->format('d/m/Y H:i') }} · {{ $stats['accountAgeDays'] }} ngày trước</strong></li>
                <li><span>Cập nhật cuối</span><strong>{{ $user->updated_at->diffForHumans() }}</strong></li>
                <li><span>Email verified</span><strong>{{ $user->email_verified_at ? '✓ ' . $user->email_verified_at->format('d/m/Y') : 'Chưa xác thực' }}</strong></li>
                <li><span>Tổng đã chi</span><strong style="color:#b55a82">{{ number_format($stats['totalSpent'], 0, ',', '.') }} ₫</strong></li>
                <li><span>Tổng sản phẩm mua</span><strong>{{ $stats['totalItems'] }}</strong></li>
            </ul>
        </section>
    </div>

    {{-- ═══ ORDER STATS ═══ --}}
    <section class="admin-card">
        <header class="admin-card__head">
            <h2>Thống kê đơn hàng ({{ $stats['total'] }})</h2>
        </header>
        <div class="admin-stats admin-stats--compact" style="padding: 18px 20px; margin: 0;">
            <div class="admin-stat-sm"><small>Đang xử lý</small><strong>{{ $stats['active'] }}</strong></div>
            <div class="admin-stat-sm"><small>Đã hoàn tất</small><strong style="color:#2f6a42">{{ $stats['received'] }}</strong></div>
            <div class="admin-stat-sm"><small>Đã huỷ</small><strong style="color:#a63652">{{ $stats['cancelled'] }}</strong></div>
            <div class="admin-stat-sm"><small>Yêu cầu trả hàng</small><strong style="color:#b15e1f">{{ $stats['returnReq'] }}</strong></div>
            <div class="admin-stat-sm"><small>Đã hoàn tiền</small><strong style="color:#5b4ba5">{{ $stats['returned'] }}</strong></div>
            <div class="admin-stat-sm"><small>Tỷ lệ huỷ</small><strong style="color:{{ $stats['cancelRatio'] >= 50 ? '#a63652' : '#6d3e55' }}">{{ $stats['cancelRatio'] }}%</strong></div>
        </div>
    </section>

    {{-- ═══ ORDER LIST ═══ --}}
    <section class="admin-card">
        <header class="admin-card__head">
            <h2>Lịch sử đơn hàng</h2>
        </header>
        @if(count($orders) === 0)
            <div class="admin-empty">
                <p>Người dùng này chưa có đơn hàng nào trong phiên admin hiện tại.</p>
                <small style="color:#a6849a">(Demo: orders lưu session-based. Khi migrate DB sẽ hiện full lịch sử.)</small>
            </div>
        @else
            <table class="admin-table admin-table--full">
                <thead>
                    <tr><th>Mã đơn</th><th>Ngày đặt</th><th>SP</th><th>Tổng</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        @php
                            $stage = \in_array($o['status'] ?? '', ['cancelled', 'returned', 'return_requested', 'received'], true)
                                ? $o['status']
                                : OrderTimeline::currentKey($o);
                            [$text, $bg, $color] = $statusMap[$stage] ?? $statusMap['pending'];
                        @endphp
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $o['id']) }}"><strong>#{{ $o['id'] }}</strong></a></td>
                            <td>{{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y H:i') }}</td>
                            <td>{{ count($o['items'] ?? []) }}</td>
                            <td><strong>{{ number_format($o['total'] ?? 0, 0, ',', '.') }}₫</strong></td>
                            <td><small>{{ strtoupper($o['payment'] ?? '—') }}</small></td>
                            <td>
                                <span style="background:{{ $bg }};color:{{ $color }};padding:3px 10px;border-radius:10px;font-size:11.5px;font-weight:700;white-space:nowrap">{{ $text }}</span>
                                @if($stage === 'cancelled' && !empty($o['cancel_reason']))
                                    <small style="display:block;color:#a63652;margin-top:2px">{{ \Illuminate\Support\Str::limit($o['cancel_reason'], 60) }}</small>
                                @elseif(\in_array($stage, ['return_requested', 'returned'], true) && !empty($o['return_reason']))
                                    <small style="display:block;color:#b15e1f;margin-top:2px">{{ \Illuminate\Support\Str::limit($o['return_reason'], 60) }}</small>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.orders.show', $o['id']) }}" class="admin-btn admin-btn--ghost">Chi tiết</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection
