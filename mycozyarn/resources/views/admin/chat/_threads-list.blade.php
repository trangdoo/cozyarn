@php $activeThreadId ??= null; @endphp
<aside class="admin-chat__sidebar">
    <header class="admin-chat__sidebar-head">
        <h2>Hộp thư</h2>
        <small>{{ count($threads) }} hội thoại</small>
    </header>
    <div class="admin-chat__list">
        @forelse($threads as $t)
            @php
                $isActive  = $t['id'] === $activeThreadId;
                $isProduct = ($t['type'] ?? '') === 'product';
            @endphp
            <a href="{{ route('admin.chat.show', ['threadId' => $t['id']]) }}"
               class="admin-chat__item @if($isActive) is-active @endif">
                <div class="admin-chat__avatar @if($isProduct) admin-chat__avatar--product @endif">
                    @if($isProduct && !empty($t['product']['image']))
                        <img src="{{ $t['product']['image'] }}" alt="">
                    @else
                        <span>U</span>
                    @endif
                </div>
                <div class="admin-chat__body">
                    <div class="admin-chat__top">
                        <strong>{{ $t['title'] }}</strong>
                        <span>{{ \Carbon\Carbon::parse($t['updated_at'])->diffForHumans(null, true) }}</span>
                    </div>
                    <p>{{ $t['last_preview'] ?? ($t['subtitle'] ?? '') }}</p>
                </div>
            </a>
        @empty
            <div class="admin-empty"><p>Chưa có tin nhắn nào.</p></div>
        @endforelse
    </div>
</aside>
