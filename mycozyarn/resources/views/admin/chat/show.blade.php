@extends('layouts.admin')

@section('title', $thread['title'] . ' — Tin nhắn')
@section('page_title', 'Tin nhắn')

@php
    $active = 'chat';
    $isProduct = ($thread['type'] ?? '') === 'product';
    $isPinned  = $thread['pinned'] ?? false;
    $isMuted   = $thread['muted']  ?? false;
    $lastReadByUser = $thread['last_read_by_user'] ?? '1970-01-01 00:00:00';
@endphp

@section('content')
<div class="admin-page admin-page--chat">
    <div class="admin-chat">
        @include('admin.chat._threads-list', ['threads' => $threads, 'activeThreadId' => $activeThreadId])

        <main class="admin-chat__main">
            <header class="admin-chat__main-head">
                <div class="admin-chat__main-head-info">
                    <strong>
                        {{ $thread['title'] }}
                        @if($isPinned)<span title="Đã ghim" style="font-size:14px">📌</span>@endif
                        @if($isMuted)<span title="Đã tắt thông báo" style="font-size:14px">🔕</span>@endif
                    </strong>
                    <small>{{ $thread['subtitle'] ?? '' }}</small>
                </div>
                <div class="admin-chat__main-actions">
                    <button type="button" class="admin-icon-btn" onclick="location.reload()" title="Làm mới">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M21 12a9 9 0 1 1-3.5-7.1M21 4v5h-5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('admin.chat.pin', $threadId) }}" class="admin-chat__action-form">
                        @csrf
                        <button type="submit" class="admin-icon-btn @if($isPinned) is-active @endif" title="{{ $isPinned ? 'Bỏ ghim' : 'Ghim hội thoại' }}">
                            <svg viewBox="0 0 24 24" fill="@if($isPinned) currentColor @else none @endif" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 2l3 6h6l-5 4 2 7-6-4-6 4 2-7-5-4h6z" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.chat.mute', $threadId) }}" class="admin-chat__action-form">
                        @csrf
                        <button type="submit" class="admin-icon-btn @if($isMuted) is-active @endif" title="{{ $isMuted ? 'Bật thông báo' : 'Tắt thông báo' }}">
                            @if($isMuted)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M6 10a6 6 0 0 1 9-5M6 10v4l-2 3h16l-2-3v-4"/><path d="M10 20a2 2 0 0 0 4 0M3 3l18 18" stroke-linecap="round"/>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M6 10a6 6 0 1 1 12 0v4l2 3H4l2-3z" stroke-linejoin="round"/>
                                    <path d="M10 20a2 2 0 0 0 4 0"/>
                                </svg>
                            @endif
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.chat.destroy', $threadId) }}" class="admin-chat__action-form"
                          onsubmit="return confirm('Xoá toàn bộ hội thoại này? Không thể hoàn tác.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="admin-icon-btn admin-icon-btn--danger" title="Xoá hội thoại">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </header>

            @if($isProduct && !empty($thread['product']['slug']))
                @php $p = $thread['product']; @endphp
                <a href="{{ route('shop.product', ['category' => $p['category'], 'product' => $p['slug']]) }}"
                   target="_blank" class="admin-chat__product-ref">
                    <img src="{{ $p['image'] }}" alt="">
                    <div>
                        <small>Hỏi về sản phẩm</small>
                        <strong>{{ $p['name'] }}</strong>
                    </div>
                </a>
            @endif

            <div class="admin-chat__messages" id="chatMessages">
                @forelse($thread['messages'] as $msg)
                    @php
                        $isShop = ($msg['sender'] ?? '') === 'shop';
                        // Read-by-user: tin shop gửi đã được user đọc (created_at ≤ last_read_by_user)
                        $seen = $isShop && ($msg['created_at'] ?? '') <= $lastReadByUser;
                    @endphp
                    <div class="admin-chat-msg admin-chat-msg--{{ $msg['sender'] }}">
                        <div class="admin-chat-msg__bubble">
                            @if(!empty($msg['image']))
                                <a href="{{ $msg['image'] }}" target="_blank">
                                    <img src="{{ $msg['image'] }}" alt="">
                                </a>
                            @endif
                            @if(!empty($msg['content']))
                                <p>{{ $msg['content'] }}</p>
                            @endif
                            <span class="admin-chat-msg__foot">
                                {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i · d/m') }}
                                @if($isShop)
                                    @if($seen)
                                        <span class="admin-chat-msg__seen" title="Đã xem">✓✓</span>
                                    @else
                                        <span class="admin-chat-msg__sent" title="Đã gửi">✓</span>
                                    @endif
                                @endif
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty"><p>Hội thoại rỗng.</p></div>
                @endforelse
            </div>

            @if($errors->any())
                <div class="admin-errors" style="margin:0 16px">
                    @foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.chat.reply', $threadId) }}" class="admin-chat__form" enctype="multipart/form-data" data-reply-form>
                @csrf

                <div class="admin-chat__preview" hidden data-reply-preview>
                    <img alt="" data-reply-preview-img>
                    <button type="button" class="admin-chat__preview-remove" data-reply-preview-remove aria-label="Bỏ ảnh">×</button>
                </div>

                <div class="admin-chat__form-row">
                    <label class="admin-chat__attach" title="Đính kèm ảnh">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <circle cx="9" cy="11" r="2"/>
                            <path d="M21 17l-5-5-9 9"/>
                        </svg>
                        <input type="file" name="image" accept="image/*" hidden data-reply-file>
                    </label>
                    <textarea name="content" rows="2" placeholder="Trả lời khách hàng..." maxlength="2000"></textarea>
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12l16-8-6 16-3-6-7-2z" stroke-linejoin="round"/></svg>
                        Gửi
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
(() => {
    // Auto scroll to bottom
    const box = document.getElementById('chatMessages');
    if (box) box.scrollTop = box.scrollHeight;

    // Image preview
    const form    = document.querySelector('[data-reply-form]');
    const fileIn  = form?.querySelector('[data-reply-file]');
    const preview = form?.querySelector('[data-reply-preview]');
    const pimg    = form?.querySelector('[data-reply-preview-img]');
    const rmBtn   = form?.querySelector('[data-reply-preview-remove]');
    fileIn?.addEventListener('change', () => {
        const f = fileIn.files?.[0];
        if (!f) { preview.hidden = true; return; }
        const r = new FileReader();
        r.onload = (e) => { pimg.src = e.target.result; preview.hidden = false; };
        r.readAsDataURL(f);
    });
    rmBtn?.addEventListener('click', () => { fileIn.value = ''; preview.hidden = true; pimg.src = ''; });

    // Quasi-realtime: auto-refresh mỗi 20s khi user không gõ và không focus textarea
    // Chỉ reload nếu: không có text trong textarea + tab đang focus
    const textarea = form?.querySelector('textarea');
    setInterval(() => {
        if (document.hidden) return;
        if (textarea && textarea.value.trim() !== '') return;
        if (document.activeElement === textarea) return;
        if (fileIn?.files?.length > 0) return;
        location.reload();
    }, 20000);
})();
</script>
@endsection
