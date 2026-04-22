@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm — CozyYarn')
@section('page_title', 'Sản phẩm')

@php $active = 'products'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Quản lý sản phẩm</h1>
            <p>{{ $products->total() }} sản phẩm · trang {{ $products->currentPage() }}/{{ max(1, $products->lastPage()) }}</p>
        </div>
        <div class="admin-page__actions">
            <a href="{{ route('admin.products.importForm') }}" class="admin-btn admin-btn--ghost" data-shortcut="I" title="Nhập dữ liệu (Alt+I)">
                ⬇ Nhập
            </a>
            <div class="admin-dropdown" data-dropdown>
                <button type="button" class="admin-btn admin-btn--ghost" data-dropdown-toggle data-shortcut="E" title="Xuất dữ liệu (Alt+E)">
                    ⬆ Xuất
                </button>
                <div class="admin-dropdown__menu" data-export-menu>
                    <a href="{{ route('admin.products.export', 'csv') }}?{{ http_build_query($filter) }}">Excel (.csv)</a>
                    <a href="{{ route('admin.products.export', 'json') }}?{{ http_build_query($filter) }}">JSON</a>
                    <a href="{{ route('admin.products.export', 'xml') }}?{{ http_build_query($filter) }}">XML</a>
                    <div class="admin-dropdown__divider"></div>
                    <a href="#" data-export-selected="csv">Đã chọn → CSV</a>
                    <a href="#" data-export-selected="json">Đã chọn → JSON</a>
                    <a href="#" data-export-selected="xml">Đã chọn → XML</a>
                </div>
            </div>
            <button type="button" class="admin-btn admin-btn--ghost" data-shortcuts-toggle title="Xem danh sách phím tắt (Alt+H)">
                ⌨ Phím tắt
            </button>
            <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn--primary" data-shortcut="N" title="Thêm mới (Alt+N)">
                ＋ Thêm mới
            </a>
        </div>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm theo tên, mã, mô tả..." title="Focus (Alt+F)" data-search-input>
        <select name="category">
            <option value="all" @selected($filter['category'] === 'all')>Tất cả danh mục</option>
            @foreach($categories as $slug => $c)
                <option value="{{ $slug }}" @selected($filter['category'] === $slug)>{{ $c['name'] }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            <option value="active" @selected($filter['status'] === 'active')>Đang bán</option>
            <option value="inactive" @selected($filter['status'] === 'inactive')>Ngưng bán</option>
        </select>
        <select name="sort">
            <option value="updated_desc" @selected($filter['sort'] === 'updated_desc')>Cập nhật mới nhất</option>
            <option value="created_desc" @selected($filter['sort'] === 'created_desc')>Tạo mới nhất</option>
            <option value="name_asc"     @selected($filter['sort'] === 'name_asc')>Tên A-Z</option>
            <option value="name_desc"    @selected($filter['sort'] === 'name_desc')>Tên Z-A</option>
            <option value="price_asc"    @selected($filter['sort'] === 'price_asc')>Giá thấp → cao</option>
            <option value="price_desc"   @selected($filter['sort'] === 'price_desc')>Giá cao → thấp</option>
        </select>
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>

        {{-- Column visibility toggle --}}
        <div class="admin-dropdown" data-dropdown>
            <button type="button" class="admin-btn admin-btn--ghost" data-dropdown-toggle title="Hiện/ẩn cột">⚙ Cột</button>
            <div class="admin-dropdown__menu admin-dropdown__menu--right" data-column-menu>
                @foreach([
                    'image' => 'Ảnh',
                    'id' => 'ProductID',
                    'name' => 'Tên',
                    'category' => 'Danh mục',
                    'price' => 'Giá',
                    'quantity' => 'Kho',
                    'unit' => 'Đơn vị',
                    'status' => 'Trạng thái',
                    'dates' => 'Ngày tạo/cập nhật',
                ] as $k => $label)
                    <label class="admin-dropdown__check">
                        <input type="checkbox" data-col-toggle="{{ $k }}" checked>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </form>

    {{-- Bulk toolbar (hidden until any row selected) --}}
    <div class="admin-bulkbar" data-bulk-bar hidden>
        <span><strong data-bulk-count>0</strong> đã chọn</span>
        <div class="admin-bulkbar__actions">
            <form method="POST" action="{{ route('admin.products.duplicateMany') }}" class="admin-bulkbar__form" data-bulk-form>
                @csrf
                <button type="submit" class="admin-btn admin-btn--ghost" data-shortcut="C" title="Sao chép các mục đã chọn (Alt+C)">
                    ⎘ Sao chép
                </button>
            </form>
            <form method="POST" action="{{ route('admin.products.bulkDelete') }}" class="admin-bulkbar__form"
                  data-bulk-form onsubmit="return confirm('Xoá các sản phẩm đã chọn?');">
                @csrf
                <button type="submit" class="admin-btn admin-btn--danger" data-shortcut="D" title="Xoá các mục đã chọn (Alt+D)">
                    🗑 Xoá
                </button>
            </form>
            <button type="button" class="admin-btn admin-btn--ghost" data-bulk-clear>Bỏ chọn</button>
        </div>
    </div>

    <div class="admin-card">
        <table class="admin-table admin-table--full admin-products-table" data-products-table>
            <thead>
                <tr>
                    <th class="admin-check-col">
                        <input type="checkbox" data-select-all title="Chọn / bỏ chọn tất cả (Alt+A)">
                    </th>
                    <th data-col="image">Ảnh</th>
                    <th data-col="id">ProductID</th>
                    <th data-col="name">Tên sản phẩm</th>
                    <th data-col="category">Danh mục</th>
                    <th data-col="price">Giá</th>
                    <th data-col="quantity">Kho</th>
                    <th data-col="unit">Đơn vị</th>
                    <th data-col="status">Trạng thái</th>
                    <th data-col="dates">Cập nhật</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    @php $key = $p['category_slug'] . '::' . $p['slug']; @endphp
                    <tr data-row>
                        <td class="admin-check-col">
                            <input type="checkbox" name="ids[]" value="{{ $key }}" data-row-check>
                        </td>
                        <td data-col="image">
                            <div class="admin-user-cell__thumb"><img src="{{ $p['image'] ?? '/images/1.jpg' }}" alt=""></div>
                        </td>
                        <td data-col="id"><code class="admin-code">{{ $p['slug'] }}</code></td>
                        <td data-col="name">
                            <strong>{{ $p['name'] }}</strong>
                            <small style="display:block;color:#a6849a">{{ \Illuminate\Support\Str::limit($p['shortDesc'] ?? '', 50) }}</small>
                        </td>
                        <td data-col="category">{{ $categories[$p['category_slug']]['name'] ?? $p['category_slug'] }}</td>
                        <td data-col="price">
                            <strong>{{ number_format($p['price'], 0, ',', '.') }}₫</strong>
                            @if(!empty($p['oldPrice']))
                                <small style="display:block;text-decoration:line-through;color:#b09aa4">{{ number_format($p['oldPrice'], 0, ',', '.') }}₫</small>
                            @endif
                        </td>
                        <td data-col="quantity">{{ $p['quantity'] }}</td>
                        <td data-col="unit">{{ $p['unit'] }}</td>
                        <td data-col="status"><span class="admin-badge admin-badge--{{ $p['status'] ?? 'active' }}">{{ ($p['status'] ?? 'active') === 'active' ? 'Đang bán' : 'Ngưng' }}</span></td>
                        <td data-col="dates">
                            <small title="Tạo: {{ $p['created_at'] }}">{{ \Carbon\Carbon::parse($p['updated_at'])->diffForHumans() }}</small>
                        </td>
                        <td class="admin-table__actions">
                            <a href="{{ route('admin.products.show', ['category' => $p['category_slug'], 'slug' => $p['slug']]) }}" class="admin-icon-btn" title="Xem chi tiết">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.products.edit', ['category' => $p['category_slug'], 'slug' => $p['slug']]) }}" class="admin-icon-btn" title="Sửa">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 20h9M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.products.duplicate', ['category' => $p['category_slug'], 'slug' => $p['slug']]) }}">
                                @csrf
                                <button type="submit" class="admin-icon-btn" title="Sao chép">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="9" y="9" width="13" height="13" rx="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', ['category' => $p['category_slug'], 'slug' => $p['slug']]) }}"
                                  onsubmit="return confirm('Xoá sản phẩm này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-icon-btn admin-icon-btn--danger" title="Xoá">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="admin-empty"><p>Không có sản phẩm nào khớp bộ lọc.</p></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
            {{ $products->links('vendor.pagination.cozy') }}
        @endif
    </div>
</div>

{{-- Shortcuts help modal --}}
<div class="admin-modal" data-shortcuts-modal hidden>
    <div class="admin-modal__box">
        <header>
            <h2>Phím tắt</h2>
            <button type="button" class="admin-modal__close" data-shortcuts-close aria-label="Đóng">×</button>
        </header>
        <ul class="admin-shortcut-list">
            <li><kbd>Alt</kbd>+<kbd>N</kbd><span>Thêm mới</span></li>
            <li><kbd>Alt</kbd>+<kbd>F</kbd><span>Focus ô tìm kiếm</span></li>
            <li><kbd>Alt</kbd>+<kbd>A</kbd><span>Chọn / bỏ chọn tất cả</span></li>
            <li><kbd>Alt</kbd>+<kbd>D</kbd><span>Xoá các mục đã chọn</span></li>
            <li><kbd>Alt</kbd>+<kbd>C</kbd><span>Sao chép các mục đã chọn</span></li>
            <li><kbd>Alt</kbd>+<kbd>E</kbd><span>Mở menu xuất file</span></li>
            <li><kbd>Alt</kbd>+<kbd>I</kbd><span>Nhập file</span></li>
            <li><kbd>Alt</kbd>+<kbd>S</kbd><span>Lưu form (trên trang thêm/sửa)</span></li>
            <li><kbd>Alt</kbd>+<kbd>H</kbd><span>Mở bảng phím tắt này</span></li>
            <li><kbd>Esc</kbd><span>Đóng modal / huỷ</span></li>
        </ul>
    </div>
</div>

<script>
(() => {
    const tbl       = document.querySelector('[data-products-table]');
    const selectAll = document.querySelector('[data-select-all]');
    const rowChecks = () => tbl.querySelectorAll('[data-row-check]');
    const bulkBar   = document.querySelector('[data-bulk-bar]');
    const bulkCount = document.querySelector('[data-bulk-count]');
    const searchIn  = document.querySelector('[data-search-input]');

    /* ─── 1) Checkbox + bulk bar ─── */
    function getSelectedIds() {
        return Array.from(rowChecks()).filter(c => c.checked).map(c => c.value);
    }
    function refreshBulkBar() {
        const ids = getSelectedIds();
        bulkCount.textContent = ids.length;
        bulkBar.hidden = ids.length === 0;

        // Inject hidden inputs vào các form bulk
        document.querySelectorAll('[data-bulk-form]').forEach(form => {
            form.querySelectorAll('input[name="ids[]"]').forEach(n => n.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
        });

        // Sync select-all checkbox
        const all = rowChecks();
        selectAll.checked = all.length > 0 && ids.length === all.length;
        selectAll.indeterminate = ids.length > 0 && ids.length < all.length;
    }
    selectAll?.addEventListener('change', () => {
        rowChecks().forEach(c => c.checked = selectAll.checked);
        refreshBulkBar();
    });
    rowChecks().forEach(c => c.addEventListener('change', refreshBulkBar));
    document.querySelector('[data-bulk-clear]')?.addEventListener('click', () => {
        rowChecks().forEach(c => c.checked = false);
        refreshBulkBar();
    });

    /* ─── 2) Export selected ─── */
    document.querySelectorAll('[data-export-selected]').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            const ids = getSelectedIds();
            if (ids.length === 0) { alert('Chưa chọn sản phẩm nào để xuất.'); return; }
            const fmt = a.dataset.exportSelected;
            window.location = @json(route('admin.products.export', ['format' => '__FMT__'])).replace('__FMT__', fmt) + '?ids=' + encodeURIComponent(ids.join(','));
        });
    });

    /* ─── 3) Column visibility + localStorage persist ─── */
    const STORAGE_KEY = 'admin-products-cols';
    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    document.querySelectorAll('[data-col-toggle]').forEach(chk => {
        const col = chk.dataset.colToggle;
        if (saved[col] === false) chk.checked = false;
        const apply = () => {
            tbl.querySelectorAll(`[data-col="${col}"]`).forEach(el => {
                el.style.display = chk.checked ? '' : 'none';
            });
            saved[col] = chk.checked;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(saved));
        };
        chk.addEventListener('change', apply);
        apply();
    });

    /* ─── 4) Dropdowns ─── */
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

    /* ─── 5) Shortcuts modal ─── */
    const modal = document.querySelector('[data-shortcuts-modal]');
    const openModal = () => { modal.hidden = false; };
    const closeModal = () => { modal.hidden = true; };
    document.querySelector('[data-shortcuts-toggle]')?.addEventListener('click', openModal);
    document.querySelector('[data-shortcuts-close]')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    /* ─── 6) Keyboard shortcuts ─── */
    document.addEventListener('keydown', (e) => {
        // Bỏ qua khi đang gõ text
        const inField = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName);

        if (e.key === 'Escape') {
            if (!modal.hidden) { closeModal(); return; }
            document.querySelectorAll('[data-dropdown].is-open').forEach(w => w.classList.remove('is-open'));
            return;
        }

        if (!e.altKey) return;

        const k = e.key.toLowerCase();
        // Các phím cho phép khi đang gõ trong search
        const allowInField = ['s'];
        if (inField && !allowInField.includes(k)) return;

        switch (k) {
            case 'n': e.preventDefault(); document.querySelector('[data-shortcut="N"]')?.click(); break;
            case 'f': e.preventDefault(); searchIn?.focus(); searchIn?.select(); break;
            case 'h': e.preventDefault(); openModal(); break;
            case 'e': e.preventDefault(); document.querySelector('[data-shortcut="E"]')?.click(); break;
            case 'i': e.preventDefault(); window.location = document.querySelector('[data-shortcut="I"]')?.href; break;
            case 'a':
                e.preventDefault();
                selectAll.checked = !selectAll.checked;
                rowChecks().forEach(c => c.checked = selectAll.checked);
                refreshBulkBar();
                break;
            case 'd':
                e.preventDefault();
                if (getSelectedIds().length === 0) { alert('Chưa chọn sản phẩm nào.'); return; }
                document.querySelector('[data-shortcut="D"]')?.closest('form')?.requestSubmit();
                break;
            case 'c':
                e.preventDefault();
                if (getSelectedIds().length === 0) { alert('Chưa chọn sản phẩm nào.'); return; }
                document.querySelector('[data-shortcut="C"]')?.closest('form')?.requestSubmit();
                break;
        }
    });

    refreshBulkBar();
})();
</script>
@endsection
