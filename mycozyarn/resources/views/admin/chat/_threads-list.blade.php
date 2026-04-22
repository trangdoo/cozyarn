@php $activeThreadId ??= null; @endphp
<aside class="admin-chat__sidebar">
    <header class="admin-chat__sidebar-head">
        <h2>Hộp thư</h2>
        <small>{{ count($threads) }} hội thoại</small>
    </header>

    <div class="admin-chat__search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/>
        </svg>
        <input type="search" placeholder="Tìm hội thoại..." data-chat-search>
    </div>

    <div class="admin-chat__list" data-chat-list>
        @forelse($threads as $t)
            @php
                $isActive  = $t['id'] === $activeThreadId;
                $isProduct = ($t['type'] ?? '') === 'product';
                $isPinned  = $t['pinned'] ?? false;
                $isMuted   = $t['muted']  ?? false;
                $unread    = $t['unread_count'] ?? 0;
                $searchKey = mb_strtolower(($t['title'] ?? '') . ' ' . ($t['last_preview'] ?? '') . ' ' . ($t['subtitle'] ?? ''));
            @endphp
            <a href="{{ route('admin.chat.show', ['threadId' => $t['id']]) }}"
               class="admin-chat__item @if($isActive) is-active @endif @if($unread) has-unread @endif"
               data-chat-thread
               data-search="{{ $searchKey }}">
                <div class="admin-chat__avatar @if($isProduct) admin-chat__avatar--product @endif">
                    @if($isProduct && !empty($t['product']['image']))
                        <img src="{{ $t['product']['image'] }}" alt="">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($t['title'] ?? 'U', 0, 1)) }}</span>
                    @endif
                </div>
                <div class="admin-chat__body">
                    <div class="admin-chat__top">
                        <strong>
                            @if($isPinned)<span class="admin-chat__pin-mark" title="Đã ghim">📌</span>@endif
                            {{ $t['title'] }}
                        </strong>
                        <span>{{ \Carbon\Carbon::parse($t['updated_at'])->diffForHumans(null, true) }}</span>
                    </div>
                    <p>{{ $t['last_preview'] ?? ($t['subtitle'] ?? '') }}</p>
                </div>
                <div class="admin-chat__meta">
                    @if($unread > 0)
                        <span class="admin-chat__unread">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                    @if($isMuted)
                        <svg class="admin-chat__muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" title="Đã tắt thông báo">
                            <path d="M6 10a6 6 0 0 1 9-5M6 10v4l-2 3h16l-2-3v-4"/><path d="M10 20a2 2 0 0 0 4 0M3 3l18 18"/>
                        </svg>
                    @endif
                </div>
            </a>
        @empty
            <div class="admin-empty"><p>Chưa có hội thoại nào.</p></div>
        @endforelse
        <div class="admin-chat__no-results" data-chat-no-results hidden>
            <p>Không tìm thấy hội thoại khớp từ khoá.</p>
        </div>
    </div>
</aside>

<script>
(() => {
    const input = document.querySelector('[data-chat-search]');
    const items = document.querySelectorAll('[data-chat-thread]');
    const noRes = document.querySelector('[data-chat-no-results]');
    if (!input) return;
    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        let visible = 0;
        items.forEach(el => {
            const match = !q || el.dataset.search.includes(q);
            el.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        noRes.hidden = visible !== 0 || items.length === 0;
    });
})();
</script>
