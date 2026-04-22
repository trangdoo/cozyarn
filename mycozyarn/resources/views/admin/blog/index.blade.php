@extends('layouts.admin')

@section('title', 'Quản lý blog — CozyYarn')
@section('page_title', 'Blog')

@php
    $active = 'blog';
    $featuredList = session('admin_blogs_featured', []);
    $now = now()->toDateTimeString();
@endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Quản lý bài viết</h1>
            <p>{{ $stats['total'] }} bài · {{ $stats['published'] }} đã đăng · {{ $stats['scheduled'] }} hẹn giờ</p>
        </div>
        <div class="admin-page__actions">
            <button type="button" class="admin-btn admin-btn--ghost" data-shortcuts-toggle title="Phím tắt (Alt+H)">⌨ Phím tắt</button>
            <a href="{{ route('admin.blog.create') }}" class="admin-btn admin-btn--primary" data-shortcut="N" title="Viết bài mới (Alt+N)">＋ Viết bài</a>
        </div>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm theo tiêu đề, nội dung..." data-search-input>
        <select name="category">
            <option value="all" @selected($filter['category'] === 'all')>Tất cả danh mục</option>
            @foreach($categories as $slug => $c)
                <option value="{{ $slug }}" @selected($filter['category'] === $slug)>{{ $c['name'] }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            <option value="published" @selected($filter['status'] === 'published')>Đã đăng</option>
            <option value="scheduled" @selected($filter['status'] === 'scheduled')>Hẹn giờ</option>
        </select>
        <input type="hidden" name="tag" value="{{ $filter['tag'] }}">
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    {{-- Tag pills --}}
    @if(!empty($tags))
        <div class="admin-tags-filter">
            <span class="admin-tags-filter__label">Tag phổ biến:</span>
            <a href="{{ route('admin.blog.index', array_merge(request()->query(), ['tag' => ''])) }}"
               class="admin-tag-pill @if($filter['tag'] === '') is-active @endif">Tất cả</a>
            @foreach($tags as $t => $cnt)
                <a href="{{ route('admin.blog.index', array_merge(request()->query(), ['tag' => $t])) }}"
                   class="admin-tag-pill @if($filter['tag'] === $t) is-active @endif">
                    #{{ $t }} <small>({{ $cnt }})</small>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Bulk toolbar --}}
    <div class="admin-bulkbar" data-bulk-bar hidden>
        <span><strong data-bulk-count>0</strong> đã chọn</span>
        <div class="admin-bulkbar__actions">
            <form method="POST" action="{{ route('admin.blog.bulkDelete') }}" class="admin-bulkbar__form"
                  data-bulk-form onsubmit="return confirm('Xoá các bài viết đã chọn?');">
                @csrf
                <button type="submit" class="admin-btn admin-btn--danger">🗑 Xoá đã chọn</button>
            </form>
            <button type="button" class="admin-btn admin-btn--ghost" data-bulk-clear>Bỏ chọn</button>
        </div>
    </div>

    <div class="admin-card">
        <table class="admin-table admin-table--full" data-blog-table>
            <thead>
                <tr>
                    <th class="admin-check-col"><input type="checkbox" data-select-all></th>
                    <th>Bài viết</th>
                    <th>Danh mục</th>
                    <th>Tác giả</th>
                    <th>Đăng lúc</th>
                    <th>Tags</th>
                    <th>Nổi bật</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $p)
                    @php
                        $scheduled = ($p['publish_at'] ?? $p['date']) > $now;
                        $isFeatured = ($p['featured'] ?? false) || \in_array($p['slug'], $featuredList, true);
                    @endphp
                    <tr>
                        <td class="admin-check-col">
                            <input type="checkbox" name="slugs[]" value="{{ $p['slug'] }}" data-row-check>
                        </td>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-user-cell__thumb">
                                    <img src="{{ $p['cover'] ?? '/images/1.jpg' }}" alt="">
                                </div>
                                <div>
                                    <strong>{{ $p['title'] }}</strong>
                                    <small>{{ \Illuminate\Support\Str::limit($p['excerpt'] ?? '', 80) }}</small>
                                    @if($scheduled)
                                        <span class="admin-sched-badge">⏱ Hẹn {{ \Carbon\Carbon::parse($p['publish_at'])->format('H:i · d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $categories[$p['category']]['name'] ?? $p['category'] }}</td>
                        <td>{{ $p['author'] ?? '—' }}</td>
                        <td>
                            @if($scheduled)
                                <small style="color:#b15e1f">{{ \Carbon\Carbon::parse($p['publish_at'])->format('d/m/Y H:i') }}</small>
                            @else
                                <small>{{ \Carbon\Carbon::parse($p['publish_at'] ?? $p['date'])->format('d/m/Y') }}</small>
                            @endif
                        </td>
                        <td>
                            @foreach(array_slice($p['tags'] ?? [], 0, 3) as $t)
                                <span class="admin-tag-mini">#{{ $t }}</span>
                            @endforeach
                            @if(count($p['tags'] ?? []) > 3)
                                <small style="color:#a6849a">+{{ count($p['tags']) - 3 }}</small>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.blog.featured', $p['slug']) }}">
                                @csrf
                                <button type="submit" class="admin-star @if($isFeatured) is-on @endif" aria-label="Toggle nổi bật">★</button>
                            </form>
                        </td>
                        <td class="admin-table__actions">
                            <a href="{{ route('admin.blog.show', $p['slug']) }}" class="admin-icon-btn" title="Xem chi tiết">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.blog.edit', $p['slug']) }}" class="admin-icon-btn" title="Sửa">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $p['slug']) }}"
                                  onsubmit="return confirm('Xoá bài viết này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-icon-btn admin-icon-btn--danger" title="Xoá">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-empty"><p>Không có bài viết nào.</p></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($posts->hasPages())
            {{ $posts->links('vendor.pagination.cozy') }}
        @endif
    </div>
</div>

{{-- Shortcuts modal --}}
<div class="admin-modal" data-shortcuts-modal hidden>
    <div class="admin-modal__box">
        <header>
            <h2>Phím tắt</h2>
            <button type="button" class="admin-modal__close" data-shortcuts-close>×</button>
        </header>
        <ul class="admin-shortcut-list">
            <li><kbd>Alt</kbd>+<kbd>N</kbd><span>Viết bài mới</span></li>
            <li><kbd>Alt</kbd>+<kbd>F</kbd><span>Focus tìm kiếm</span></li>
            <li><kbd>Alt</kbd>+<kbd>A</kbd><span>Chọn / bỏ chọn tất cả</span></li>
            <li><kbd>Alt</kbd>+<kbd>H</kbd><span>Mở bảng này</span></li>
            <li><kbd>Esc</kbd><span>Đóng</span></li>
        </ul>
    </div>
</div>

<script>
(() => {
    const tbl = document.querySelector('[data-blog-table]');
    const selectAll = document.querySelector('[data-select-all]');
    const rowChecks = () => tbl.querySelectorAll('[data-row-check]');
    const bulkBar = document.querySelector('[data-bulk-bar]');
    const bulkCount = document.querySelector('[data-bulk-count]');
    const searchIn = document.querySelector('[data-search-input]');

    function getSelectedSlugs() {
        return Array.from(rowChecks()).filter(c => c.checked).map(c => c.value);
    }
    function refreshBulk() {
        const ids = getSelectedSlugs();
        bulkCount.textContent = ids.length;
        bulkBar.hidden = ids.length === 0;
        document.querySelectorAll('[data-bulk-form]').forEach(form => {
            form.querySelectorAll('input[name="slugs[]"]').forEach(n => n.remove());
            ids.forEach(id => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = 'slugs[]'; i.value = id;
                form.appendChild(i);
            });
        });
        const all = rowChecks();
        selectAll.checked = all.length > 0 && ids.length === all.length;
        selectAll.indeterminate = ids.length > 0 && ids.length < all.length;
    }
    selectAll?.addEventListener('change', () => {
        rowChecks().forEach(c => c.checked = selectAll.checked);
        refreshBulk();
    });
    rowChecks().forEach(c => c.addEventListener('change', refreshBulk));
    document.querySelector('[data-bulk-clear]')?.addEventListener('click', () => {
        rowChecks().forEach(c => c.checked = false);
        refreshBulk();
    });

    const modal = document.querySelector('[data-shortcuts-modal]');
    document.querySelector('[data-shortcuts-toggle]')?.addEventListener('click', () => modal.hidden = false);
    document.querySelector('[data-shortcuts-close]')?.addEventListener('click', () => modal.hidden = true);
    modal?.addEventListener('click', (e) => { if (e.target === modal) modal.hidden = true; });

    document.addEventListener('keydown', (e) => {
        const inField = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName);
        if (e.key === 'Escape') { modal.hidden = true; return; }
        if (!e.altKey) return;
        const k = e.key.toLowerCase();
        if (inField && k !== 's') return;
        switch (k) {
            case 'n': e.preventDefault(); document.querySelector('[data-shortcut="N"]')?.click(); break;
            case 'f': e.preventDefault(); searchIn?.focus(); searchIn?.select(); break;
            case 'h': e.preventDefault(); modal.hidden = false; break;
            case 'a':
                e.preventDefault();
                selectAll.checked = !selectAll.checked;
                rowChecks().forEach(c => c.checked = selectAll.checked);
                refreshBulk();
                break;
        }
    });

    refreshBulk();
})();
</script>
@endsection
