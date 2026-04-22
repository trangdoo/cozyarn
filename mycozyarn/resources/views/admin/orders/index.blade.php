@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng — CozyYarn')
@section('page_title', 'Đơn hàng')

@php
    use App\Support\OrderTimeline;
    $active = 'orders';
    $statusMap = [
        'pending' => ['Chờ xác nhận', '#fff6cc', '#9a7a1f'],
        'placed'  => ['Mới đặt', '#fff6cc', '#9a7a1f'],
        'confirmed' => ['Chờ lấy hàng', '#e0f0ff', '#2c5580'],
        'shipping' => ['Đang giao', '#fde4ee', '#b55a82'],
        'delivered' => ['Đã giao', '#c3e8d5', '#3d7a52'],
        'received' => ['Hoàn tất', '#d4efdb', '#2f6a42'],
        'cancelled' => ['Đã huỷ', '#ffe0e0', '#a63652'],
        'return_requested' => ['Yêu cầu trả', '#fff0d9', '#b15e1f'],
        'returned' => ['Đã hoàn tiền', '#e4dcf5', '#5b4ba5'],
    ];
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Quản lý đơn hàng</h1>
            <p>{{ $stats['all'] }} đơn · {{ $stats['pending'] }} chờ xử lý · {{ $stats['shipping'] }} đang giao</p>
        </div>
        <div class="admin-page__actions">
            <button type="button" class="admin-btn admin-btn--ghost" data-shortcuts-toggle title="Phím tắt (Alt+H)">⌨ Phím tắt</button>
            <div class="admin-dropdown" data-dropdown>
                <button type="button" class="admin-btn admin-btn--ghost" data-dropdown-toggle data-shortcut="E" title="Xuất file (Alt+E)">⬆ Xuất</button>
                <div class="admin-dropdown__menu admin-dropdown__menu--right">
                    <a href="{{ route('admin.orders.export', 'csv') }}?{{ http_build_query($filter) }}">Excel (.csv)</a>
                    <a href="{{ route('admin.orders.export', 'json') }}?{{ http_build_query($filter) }}">JSON</a>
                    <div class="admin-dropdown__divider"></div>
                    <a href="#" data-export-selected="csv">Đã chọn → CSV</a>
                    <a href="#" data-export-selected="json">Đã chọn → JSON</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue stats --}}
    <div class="admin-stats">
        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M6 6h9a3 3 0 0 1 0 6h-6a3 3 0 0 0 0 6h9"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Doanh thu hôm nay</small>
                <strong>{{ number_format($stats['revenueToday'], 0, ',', '.') }}₫</strong>
                <span class="admin-stat__trend">từ đơn chưa huỷ</span>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--pink">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3h18v4H3zM5 7v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Doanh thu tháng này</small>
                <strong>{{ number_format($stats['revenueMonth'], 0, ',', '.') }}₫</strong>
                <span class="admin-stat__trend">{{ now()->format('m/Y') }}</span>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><path d="M7 15l4-4 4 4 5-6"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Tổng doanh thu</small>
                <strong>{{ number_format($stats['revenueAll'], 0, ',', '.') }}₫</strong>
                <span class="admin-stat__trend">luỹ kế</span>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat__icon admin-stat__icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/></svg>
            </div>
            <div class="admin-stat__body">
                <small>Đã hoàn tất</small>
                <strong>{{ $stats['delivered'] }}</strong>
                <span class="admin-stat__trend">đơn giao thành công</span>
            </div>
        </div>
    </div>

    <div class="admin-stats admin-stats--compact">
        <div class="admin-stat-sm"><small>Tổng đơn</small><strong>{{ $stats['all'] }}</strong></div>
        <div class="admin-stat-sm"><small>Chờ xác nhận</small><strong style="color:#9a7a1f">{{ $stats['pending'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đang giao</small><strong style="color:#b55a82">{{ $stats['shipping'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đã huỷ</small><strong style="color:#a63652">{{ $stats['cancelled'] }}</strong></div>
        <div class="admin-stat-sm"><small>Yêu cầu trả</small><strong style="color:#b15e1f">{{ $stats['returnReq'] }}</strong></div>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm mã đơn, tên, SĐT, email..." data-search-input>
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            @foreach($statusMap as $k => $v)
                <option value="{{ $k }}" @selected($filter['status'] === $k)>{{ $v[0] }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ $filter['from'] }}" title="Từ ngày">
        <input type="date" name="to"   value="{{ $filter['to'] }}" title="Đến ngày">
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    {{-- Bulk toolbar --}}
    <div class="admin-bulkbar" data-bulk-bar hidden>
        <span><strong data-bulk-count>0</strong> đã chọn</span>
        <div class="admin-bulkbar__actions">
            <form method="POST" action="{{ route('admin.orders.bulkConfirm') }}" class="admin-bulkbar__form" data-bulk-form>
                @csrf
                <button type="submit" class="admin-btn admin-btn--success" data-shortcut="Y" title="Xác nhận các đơn đã chọn (Alt+Y)">✓ Xác nhận</button>
            </form>
            <form method="POST" action="{{ route('admin.orders.bulkDelete') }}" class="admin-bulkbar__form"
                  data-bulk-form onsubmit="return confirm('Xoá các đơn đã chọn?');">
                @csrf
                <button type="submit" class="admin-btn admin-btn--danger" data-shortcut="D" title="Xoá (Alt+D)">🗑 Xoá</button>
            </form>
            <button type="button" class="admin-btn admin-btn--ghost" data-bulk-clear>Bỏ chọn</button>
        </div>
    </div>

    <div class="admin-card">
        <table class="admin-table admin-table--full" data-orders-table>
            <thead>
                <tr>
                    <th class="admin-check-col"><input type="checkbox" data-select-all></th>
                    <th>Mã đơn</th>
                    <th>Khách</th>
                    <th>Ngày</th>
                    <th>SP</th>
                    <th>Tổng</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    @php
                        $stage = \in_array($o['status'] ?? '', ['cancelled', 'returned', 'return_requested', 'received'], true)
                            ? $o['status']
                            : OrderTimeline::currentKey($o);
                        [$text, $bg, $color] = $statusMap[$stage] ?? $statusMap['pending'];
                        $isPending = \in_array($stage, ['pending', 'placed'], true);
                    @endphp
                    <tr>
                        <td class="admin-check-col">
                            <input type="checkbox" name="ids[]" value="{{ $o['id'] }}" data-row-check data-stage="{{ $stage }}">
                        </td>
                        <td><a href="{{ route('admin.orders.show', $o['id']) }}"><strong>#{{ $o['id'] }}</strong></a></td>
                        <td>
                            <strong>{{ $o['name'] ?? '—' }}</strong><br>
                            <small>{{ $o['phone'] ?? '' }}</small>
                        </td>
                        <td><small>{{ \Carbon\Carbon::parse($o['created_at'])->format('H:i · d/m') }}</small></td>
                        <td>{{ count($o['items'] ?? []) }}</td>
                        <td><strong>{{ number_format($o['total'] ?? 0, 0, ',', '.') }}₫</strong></td>
                        <td><small>{{ strtoupper($o['payment'] ?? '—') }}</small></td>
                        <td><span style="background:{{ $bg }};color:{{ $color }};padding:3px 10px;border-radius:10px;font-size:11.5px;font-weight:700;white-space:nowrap">{{ $text }}</span></td>
                        <td class="admin-table__actions">
                            @if($isPending)
                                <form method="POST" action="{{ route('admin.orders.confirm', $o['id']) }}">
                                    @csrf
                                    <button type="submit" class="admin-icon-btn" title="Xác nhận đơn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12l5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.orders.show', $o['id']) }}" class="admin-icon-btn" title="Chi tiết">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.orders.destroy', $o['id']) }}" onsubmit="return confirm('Xoá đơn này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-icon-btn admin-icon-btn--danger" title="Xoá">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="admin-empty"><p>Chưa có đơn nào khớp bộ lọc.</p></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
            {{ $orders->links('vendor.pagination.cozy') }}
        @endif
    </div>
</div>

{{-- Shortcuts modal --}}
<div class="admin-modal" data-shortcuts-modal hidden>
    <div class="admin-modal__box">
        <header><h2>Phím tắt</h2><button type="button" class="admin-modal__close" data-shortcuts-close>×</button></header>
        <ul class="admin-shortcut-list">
            <li><kbd>Alt</kbd>+<kbd>F</kbd><span>Focus tìm kiếm</span></li>
            <li><kbd>Alt</kbd>+<kbd>A</kbd><span>Chọn / bỏ chọn tất cả</span></li>
            <li><kbd>Alt</kbd>+<kbd>Y</kbd><span>Xác nhận đơn đã chọn</span></li>
            <li><kbd>Alt</kbd>+<kbd>D</kbd><span>Xoá đơn đã chọn</span></li>
            <li><kbd>Alt</kbd>+<kbd>E</kbd><span>Mở menu xuất file</span></li>
            <li><kbd>Alt</kbd>+<kbd>H</kbd><span>Mở bảng này</span></li>
            <li><kbd>Esc</kbd><span>Đóng</span></li>
        </ul>
    </div>
</div>

<script>
(() => {
    const tbl = document.querySelector('[data-orders-table]');
    const selectAll = document.querySelector('[data-select-all]');
    const rowChecks = () => tbl.querySelectorAll('[data-row-check]');
    const bulkBar = document.querySelector('[data-bulk-bar]');
    const bulkCount = document.querySelector('[data-bulk-count]');
    const searchIn = document.querySelector('[data-search-input]');

    function getIds() { return Array.from(rowChecks()).filter(c => c.checked).map(c => c.value); }
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

    // Export selected
    document.querySelectorAll('[data-export-selected]').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            const ids = getIds();
            if (ids.length === 0) { alert('Chưa chọn đơn nào để xuất.'); return; }
            const fmt = a.dataset.exportSelected;
            window.location = @json(route('admin.orders.export', ['format' => '__FMT__'])).replace('__FMT__', fmt) + '?ids=' + encodeURIComponent(ids.join(','));
        });
    });

    // Dropdown
    document.querySelectorAll('[data-dropdown]').forEach(wrap => {
        const toggle = wrap.querySelector('[data-dropdown-toggle]');
        toggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('[data-dropdown].is-open').forEach(w => { if (w !== wrap) w.classList.remove('is-open'); });
            wrap.classList.toggle('is-open');
        });
    });
    document.addEventListener('click', (e) => {
        document.querySelectorAll('[data-dropdown].is-open').forEach(w => {
            if (!w.contains(e.target)) w.classList.remove('is-open');
        });
    });

    // Modal
    const modal = document.querySelector('[data-shortcuts-modal]');
    document.querySelector('[data-shortcuts-toggle]')?.addEventListener('click', () => modal.hidden = false);
    document.querySelector('[data-shortcuts-close]')?.addEventListener('click', () => modal.hidden = true);
    modal?.addEventListener('click', (e) => { if (e.target === modal) modal.hidden = true; });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        const inField = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName);
        if (e.key === 'Escape') { modal.hidden = true; document.querySelectorAll('[data-dropdown].is-open').forEach(w => w.classList.remove('is-open')); return; }
        if (!e.altKey) return;
        const k = e.key.toLowerCase();
        if (inField) return;
        switch (k) {
            case 'f': e.preventDefault(); searchIn?.focus(); searchIn?.select(); break;
            case 'h': e.preventDefault(); modal.hidden = false; break;
            case 'e': e.preventDefault(); document.querySelector('[data-shortcut="E"]')?.click(); break;
            case 'a':
                e.preventDefault();
                selectAll.checked = !selectAll.checked;
                rowChecks().forEach(c => c.checked = selectAll.checked);
                refresh();
                break;
            case 'y':
                e.preventDefault();
                if (getIds().length === 0) { alert('Chưa chọn đơn nào.'); return; }
                document.querySelector('[data-shortcut="Y"]')?.closest('form')?.requestSubmit();
                break;
            case 'd':
                e.preventDefault();
                if (getIds().length === 0) { alert('Chưa chọn đơn nào.'); return; }
                document.querySelector('[data-shortcut="D"]')?.closest('form')?.requestSubmit();
                break;
        }
    });

    refresh();
})();
</script>
@endsection
