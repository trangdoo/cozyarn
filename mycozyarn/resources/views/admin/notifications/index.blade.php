@extends('layouts.admin')

@section('title', 'Thông báo — CozyYarn')
@section('page_title', 'Thông báo hệ thống')

@php
    $active = 'notifications';
    $now = now()->toDateTimeString();
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Thông báo</h1>
            <p>{{ $stats['total'] }} thông báo · {{ $stats['sent'] }} đã gửi · {{ $stats['scheduled'] }} hẹn giờ</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="admin-btn admin-btn--primary">＋ Soạn thông báo</a>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm theo tiêu đề, nội dung...">
        <select name="type">
            <option value="all" @selected($filter['type'] === 'all')>Tất cả loại</option>
            <option value="promo" @selected($filter['type'] === 'promo')>Khuyến mãi</option>
            <option value="order" @selected($filter['type'] === 'order')>Đơn hàng</option>
        </select>
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            <option value="sent" @selected($filter['status'] === 'sent')>Đã gửi</option>
            <option value="scheduled" @selected($filter['status'] === 'scheduled')>Hẹn giờ</option>
        </select>
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    {{-- Bulk toolbar --}}
    <div class="admin-bulkbar" data-bulk-bar hidden>
        <span><strong data-bulk-count>0</strong> đã chọn</span>
        <div class="admin-bulkbar__actions">
            <form method="POST" action="{{ route('admin.notifications.bulkDelete') }}" class="admin-bulkbar__form"
                  data-bulk-form onsubmit="return confirm('Xoá các thông báo đã chọn?');">
                @csrf
                <button type="submit" class="admin-btn admin-btn--danger">🗑 Xoá đã chọn</button>
            </form>
            <button type="button" class="admin-btn admin-btn--ghost" data-bulk-clear>Bỏ chọn</button>
        </div>
    </div>

    <div class="admin-card">
        <table class="admin-table admin-table--full" data-notif-table>
            <thead>
                <tr>
                    <th class="admin-check-col"><input type="checkbox" data-select-all></th>
                    <th>Tiêu đề</th>
                    <th>Loại</th>
                    <th>Gửi đến</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Đã nhận</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                    @php
                        $isScheduled = ($n['send_at'] ?? $now) > $now;
                        $recipText = match (true) {
                            ($n['recipients'] ?? null) === 'all'         => '🌐 Toàn hệ thống',
                            ($n['recipients'] ?? null) === 'role:user'   => '👥 Tất cả user',
                            ($n['recipients'] ?? null) === 'role:admin'  => '⚙ Admin',
                            \is_array($n['recipients'] ?? null)          => '👤 ' . \count($n['recipients']) . ' người cụ thể',
                            default => '—',
                        };
                        $deliveredCount = \count($n['delivered_to'] ?? []);
                    @endphp
                    <tr>
                        <td class="admin-check-col">
                            <input type="checkbox" name="ids[]" value="{{ $n['id'] }}" data-row-check>
                        </td>
                        <td>
                            <strong>{{ $n['title'] }}</strong>
                            <small style="display:block;color:#a6849a">{{ \Illuminate\Support\Str::limit($n['content'], 80) }}</small>
                        </td>
                        <td><span class="admin-badge admin-badge--{{ $n['type'] ?? 'promo' }}">{{ ($n['type'] ?? 'promo') === 'promo' ? 'Khuyến mãi' : 'Đơn hàng' }}</span></td>
                        <td><small>{{ $recipText }}</small></td>
                        <td>
                            @if($isScheduled)
                                <span class="admin-sched-badge">⏱ {{ \Carbon\Carbon::parse($n['send_at'])->format('H:i · d/m/Y') }}</span>
                            @else
                                <small>{{ \Carbon\Carbon::parse($n['send_at'] ?? $n['created_at'])->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td>
                            @if($isScheduled)
                                <span class="admin-badge admin-badge--pending">Chờ gửi</span>
                            @else
                                <span class="admin-badge admin-badge--active">Đã gửi</span>
                            @endif
                        </td>
                        <td><small>{{ $deliveredCount }} người</small></td>
                        <td class="admin-table__actions">
                            @if($isScheduled)
                                <a href="{{ route('admin.notifications.edit', $n['id']) }}" class="admin-icon-btn" title="Sửa">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('admin.notifications.destroy', $n['id']) }}"
                                  onsubmit="return confirm('Xoá thông báo này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-icon-btn admin-icon-btn--danger" title="Xoá">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-empty">
                        <p>Chưa có thông báo nào. <a href="{{ route('admin.notifications.create') }}">Soạn thông báo đầu tiên →</a></p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($notifications->hasPages())
            {{ $notifications->links('vendor.pagination.cozy') }}
        @endif
    </div>
</div>

<script>
(() => {
    const tbl = document.querySelector('[data-notif-table]');
    const selectAll = document.querySelector('[data-select-all]');
    const rowChecks = () => tbl.querySelectorAll('[data-row-check]');
    const bulkBar = document.querySelector('[data-bulk-bar]');
    const bulkCount = document.querySelector('[data-bulk-count]');

    function getIds() {
        return Array.from(rowChecks()).filter(c => c.checked).map(c => c.value);
    }
    function refresh() {
        const ids = getIds();
        bulkCount.textContent = ids.length;
        bulkBar.hidden = ids.length === 0;
        document.querySelectorAll('[data-bulk-form]').forEach(form => {
            form.querySelectorAll('input[name="ids[]"]').forEach(n => n.remove());
            ids.forEach(id => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = 'ids[]'; i.value = id;
                form.appendChild(i);
            });
        });
        const all = rowChecks();
        selectAll.checked = all.length > 0 && ids.length === all.length;
        selectAll.indeterminate = ids.length > 0 && ids.length < all.length;
    }
    selectAll?.addEventListener('change', () => {
        rowChecks().forEach(c => c.checked = selectAll.checked);
        refresh();
    });
    rowChecks().forEach(c => c.addEventListener('change', refresh));
    document.querySelector('[data-bulk-clear]')?.addEventListener('click', () => {
        rowChecks().forEach(c => c.checked = false);
        refresh();
    });
    refresh();
})();
</script>
@endsection
