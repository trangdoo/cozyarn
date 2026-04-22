@extends('layouts.admin')

@section('title', 'Đơn #' . $order['id'] . ' — CozyYarn')
@section('page_title', 'Chi tiết đơn hàng')

@php
    use App\Support\OrderTimeline;
    $active = 'orders';
    $statusMap = [
        'pending' => 'Chờ xác nhận', 'placed' => 'Mới đặt', 'confirmed' => 'Chờ lấy hàng',
        'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'received' => 'Hoàn tất',
        'cancelled' => 'Đã huỷ', 'return_requested' => 'Yêu cầu trả', 'returned' => 'Đã hoàn tiền',
    ];
    $rawStatus = $order['status'] ?? '';
    $stage = \in_array($rawStatus, ['cancelled', 'returned', 'return_requested', 'received'], true)
        ? $rawStatus
        : OrderTimeline::currentKey($order);
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="admin-back">← Danh sách</a>
            <h1>Đơn #{{ $order['id'] }}</h1>
            <p>Đặt lúc {{ \Carbon\Carbon::parse($order['created_at'])->format('H:i · d/m/Y') }} · <strong>{{ $statusMap[$stage] ?? $stage }}</strong></p>
        </div>
    </div>

    {{-- ═══ ACTION BAR theo stage ═══ --}}
    <section class="admin-card od-action-bar">
        <header class="admin-card__head">
            <h2>Thao tác theo trạng thái</h2>
        </header>
        <div class="od-action-bar__body">
            @if(\in_array($stage, ['pending', 'placed'], true))
                <div class="od-action-group">
                    <strong>Đơn mới — cần xác nhận</strong>
                    <div class="od-action-group__btns">
                        <form method="POST" action="{{ route('admin.orders.confirm', $order['id']) }}">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--success">✓ Xác nhận đơn</button>
                        </form>
                        <form method="POST" action="{{ route('admin.orders.approveCancel', $order['id']) }}" onsubmit="return confirm('Duyệt huỷ đơn?');">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--danger">✕ Huỷ đơn</button>
                        </form>
                    </div>
                </div>
            @elseif($stage === 'confirmed')
                <div class="od-action-group">
                    <strong>Đã xác nhận — sẵn sàng giao</strong>
                    <div class="od-action-group__btns">
                        <form method="POST" action="{{ route('admin.orders.ship', $order['id']) }}">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--primary">🚚 Bàn giao vận chuyển</button>
                        </form>
                        <form method="POST" action="{{ route('admin.orders.approveCancel', $order['id']) }}" onsubmit="return confirm('Huỷ đơn đã xác nhận?');">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--danger">✕ Huỷ đơn</button>
                        </form>
                    </div>
                </div>
            @elseif($stage === 'shipping')
                <div class="od-action-group">
                    <strong>Đang giao hàng</strong>
                    <div class="od-action-group__btns">
                        <form method="POST" action="{{ route('admin.orders.deliver', $order['id']) }}">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--success">📦 Xác nhận đã giao</button>
                        </form>
                    </div>
                </div>
            @elseif($stage === 'return_requested')
                <div class="od-action-group">
                    <strong>🔔 Khách yêu cầu trả hàng & hoàn tiền</strong>
                    @if(!empty($order['return_reason']))
                        <p style="margin: 6px 0 10px; padding: 10px; background: #fff0e3; border-radius: 8px; color: #8a5e3a;">
                            <strong>Lý do:</strong> {{ $order['return_reason'] }}
                        </p>
                    @endif
                    <div class="od-action-group__btns">
                        <form method="POST" action="{{ route('admin.orders.approveReturn', $order['id']) }}" onsubmit="return confirm('Duyệt hoàn tiền cho khách?');">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn--success">💰 Duyệt hoàn tiền</button>
                        </form>
                        <form method="POST" action="{{ route('admin.orders.rejectReturn', $order['id']) }}" class="od-reject-form" data-reject-form>
                            @csrf
                            <input type="text" name="reason" placeholder="Lý do từ chối (tuỳ chọn)" maxlength="300">
                            <button type="submit" class="admin-btn admin-btn--danger">Từ chối trả hàng</button>
                        </form>
                    </div>
                </div>
            @else
                <p class="admin-empty" style="padding:10px">
                    Trạng thái hiện tại: <strong>{{ $statusMap[$stage] ?? $stage }}</strong> — đơn đã ở trạng thái cuối hoặc do user xử lý.
                </p>
            @endif

            {{-- Manual override --}}
            <details class="od-manual">
                <summary>Cập nhật trạng thái thủ công ▾</summary>
                <form method="POST" action="{{ route('admin.orders.status', $order['id']) }}" class="od-manual__form">
                    @csrf @method('PATCH')
                    <select name="status">
                        @foreach($statusMap as $k => $label)
                            <option value="{{ $k }}" @selected($k === $rawStatus)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="note" placeholder="Ghi chú (lý do đổi)" maxlength="300">
                    <button type="submit" class="admin-btn admin-btn--ghost">Lưu</button>
                </form>
            </details>
        </div>
    </section>

    <div class="admin-grid-2">
        {{-- ═══ Thông tin khách + giao hàng ═══ --}}
        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin khách hàng</h2></header>
            @if($customer)
                <div class="admin-user-head">
                    <div class="admin-user-head__avatar">
                        {{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}
                    </div>
                    <div class="admin-user-head__info">
                        <strong>{{ $customer->name }}</strong>
                        <small>{{ $customer->email }} · {{ $customer->phone ?? 'chưa có SĐT' }}</small>
                        <div class="admin-user-head__tags">
                            <a href="{{ route('admin.users.show', $customer) }}" class="admin-badge admin-badge--user">Xem hồ sơ →</a>
                            <span class="admin-badge admin-badge--{{ $customer->status }}">{{ $customer->status }}</span>
                        </div>
                    </div>
                </div>
            @endif
            <ul class="admin-info">
                <li><span>Người nhận</span><strong>{{ $order['name'] ?? '—' }}</strong></li>
                <li><span>SĐT</span><strong>{{ $order['phone'] ?? '—' }}</strong></li>
                <li><span>Địa chỉ</span><strong>{{ $order['address'] ?? '' }}, {{ $order['district'] ?? '' }}, {{ $order['province'] ?? '' }}</strong></li>
                @if(!empty($order['note']))
                    <li><span>Ghi chú</span><strong>{{ $order['note'] }}</strong></li>
                @endif
                <li><span>Thanh toán</span><strong>{{ strtoupper($order['payment'] ?? '—') }}</strong></li>
                @if(count($otherOrders) > 0)
                    <li><span>Lịch sử</span><strong>{{ count($otherOrders) }} đơn khác</strong></li>
                @endif
            </ul>
        </section>

        {{-- ═══ Lịch sử trạng thái ═══ --}}
        <section class="admin-card">
            <header class="admin-card__head"><h2>Lịch sử trạng thái</h2></header>
            @if(count($history) === 0)
                <div class="admin-empty"><p>Chưa có lịch sử thay đổi. Đơn vẫn ở trạng thái khởi tạo.</p></div>
            @else
                <ol class="od-history">
                    @foreach(array_reverse($history) as $h)
                        <li>
                            <div class="od-history__dot"></div>
                            <div class="od-history__body">
                                <strong>{{ $statusMap[$h['from']] ?? $h['from'] }} → {{ $statusMap[$h['to']] ?? $h['to'] }}</strong>
                                <p>{{ $h['note'] ?? '' }}</p>
                                <small>bởi <strong>{{ $h['by'] ?? 'Hệ thống' }}</strong> · {{ \Carbon\Carbon::parse($h['at'])->format('H:i · d/m/Y') }}</small>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    <div class="admin-grid-2">
        {{-- ═══ Sản phẩm + thanh toán ═══ --}}
        <section class="admin-card">
            <header class="admin-card__head"><h2>Sản phẩm ({{ count($order['items'] ?? []) }})</h2></header>
            <table class="admin-table">
                <thead><tr><th>SP</th><th>SL</th><th>Đơn giá</th><th>Tạm tính</th></tr></thead>
                <tbody>
                    @foreach($order['items'] ?? [] as $it)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <div class="admin-user-cell__thumb"><img src="{{ $it['image'] ?? '/images/1.jpg' }}" alt=""></div>
                                    <div>
                                        <strong>{{ $it['name'] }}</strong>
                                        @if(!empty($it['variant']) || !empty($it['size']))
                                            <small style="display:block;color:#a6849a">{{ $it['variant'] ?? '' }} {{ !empty($it['size']) ? '· ' . $it['size'] : '' }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $it['qty'] }}</td>
                            <td>{{ number_format($it['price'], 0, ',', '.') }}₫</td>
                            <td><strong>{{ number_format($it['price'] * $it['qty'], 0, ',', '.') }}₫</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding: 14px 20px; border-top: 1px solid #f5d6e3;">
                <div class="co-summary__row"><span>Tạm tính</span><strong>{{ number_format($order['subtotal'] ?? 0, 0, ',', '.') }}₫</strong></div>
                <div class="co-summary__row"><span>Phí vận chuyển</span><strong>{{ ($order['shippingFee'] ?? 0) === 0 ? 'Miễn phí' : number_format($order['shippingFee'], 0, ',', '.') . '₫' }}</strong></div>
                <div class="co-summary__total"><span>Tổng</span><strong style="color:#b55a82">{{ number_format($order['total'] ?? 0, 0, ',', '.') }}₫</strong></div>
            </div>
        </section>

        {{-- ═══ Bằng chứng trả hàng ═══ --}}
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

    {{-- ═══ Đánh giá của khách + phản hồi ═══ --}}
    @if(count($orderReviews) > 0)
        <section class="admin-card">
            <header class="admin-card__head"><h2>Đánh giá của khách ({{ count($orderReviews) }})</h2></header>
            <div class="od-reviews">
                @foreach($orderReviews as $itemKey => $review)
                    <article class="od-review">
                        <div class="od-review__head">
                            <div class="admin-user-cell__thumb"><img src="{{ $review['item']['image'] ?? '/images/1.jpg' }}" alt=""></div>
                            <div>
                                <strong>{{ $review['item']['name'] ?? '' }}</strong>
                                <div class="od-review__stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="@if($i <= ($review['rating'] ?? 0)) is-on @endif">★</span>
                                    @endfor
                                    <small>· {{ \Carbon\Carbon::parse($review['created_at'])->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </div>
                        @if(!empty($review['comment']))
                            <p class="od-review__comment">{{ $review['comment'] }}</p>
                        @endif

                        @if(!empty($review['admin_reply']))
                            <div class="od-review__reply">
                                <strong>💬 Phản hồi của shop — {{ $review['admin_reply']['by'] }}</strong>
                                <p>{{ $review['admin_reply']['content'] }}</p>
                                <small>{{ \Carbon\Carbon::parse($review['admin_reply']['created_at'])->format('H:i · d/m/Y') }}</small>
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.orders.replyReview', ['id' => $order['id'], 'item' => $itemKey]) }}" class="od-review__form">
                                @csrf
                                <textarea name="content" rows="2" placeholder="Phản hồi công khai tới khách..." required maxlength="1000"></textarea>
                                <button type="submit" class="admin-btn admin-btn--primary">Gửi phản hồi</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
