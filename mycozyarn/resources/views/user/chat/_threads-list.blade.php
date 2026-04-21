@php $activeThreadId ??= null; @endphp
<aside class="chat-app__sidebar">
    <header class="chat-app__sidebar-head">
        <h2>Tin nhắn</h2>
        <small>{{ count($threads) }} hội thoại</small>
    </header>
    <div class="chat-app__thread-list">
        @foreach($threads as $t)
            @php
                $isActive  = $t['id'] === $activeThreadId;
                $isProduct = ($t['type'] ?? '') === 'product';
            @endphp
            <a href="{{ route('user.chat.thread', ['threadId' => $t['id']]) }}"
               class="chat-thread-item @if($isActive) is-active @endif">
                <div class="chat-thread-item__avatar @if($isProduct) chat-thread-item__avatar--product @endif">
                    @if($isProduct && !empty($t['product']['image']))
                        <img src="{{ $t['product']['image'] }}" alt="{{ $t['title'] }}">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($t['title'], 0, 1)) }}</span>
                    @endif
                </div>
                <div class="chat-thread-item__body">
                    <div class="chat-thread-item__head">
                        <strong>{{ $t['title'] }}</strong>
                        <span class="chat-thread-item__time">
                            {{ \Carbon\Carbon::parse($t['updated_at'])->diffForHumans(null, true) }}
                        </span>
                    </div>
                    <p class="chat-thread-item__preview">
                        @if(!empty($t['last_preview']))
                            {{ $t['last_preview'] }}
                        @else
                            <em>{{ $t['subtitle'] ?: 'Bấm để bắt đầu' }}</em>
                        @endif
                    </p>
                    @if($isProduct)
                        <span class="chat-thread-item__tag">Về sản phẩm</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</aside>
