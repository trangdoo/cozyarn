@extends('layouts.public')

@section('title', $thread['title'] . ' — Tin nhắn · CozyYarn')

@section('content')
@php
    $isProduct = ($thread['type'] ?? '') === 'product';
    $product   = $thread['product'] ?? null;
@endphp
<section class="chat-app">
    @include('user.chat._threads-list', ['threads' => $threads, 'activeThreadId' => $activeThreadId])

    <main class="chat-app__main">
        <header class="chat-app__main-head">
            <a href="{{ route('user.chat.inbox') }}" class="chat-app__back" aria-label="Quay lại hộp thư">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <div class="chat-app__main-head-info">
                <strong>{{ $thread['title'] }}</strong>
                <small>{{ $thread['subtitle'] ?: 'Hội thoại' }}</small>
            </div>
        </header>

        @if($isProduct && $product && !empty($product['slug']))
            <a href="{{ route('shop.product', ['category' => $product['category'], 'product' => $product['slug']]) }}"
               class="chat-product-ref">
                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                <div class="chat-product-ref__body">
                    <small>Đang trò chuyện về sản phẩm</small>
                    <strong>{{ $product['name'] }}</strong>
                    @if(!empty($product['price']))
                        <span class="chat-product-ref__price">{{ number_format($product['price'], 0, ',', '.') }} ₫</span>
                    @endif
                </div>
                <span class="chat-product-ref__view">Xem sản phẩm →</span>
            </a>
        @endif

        <div class="chat-messages" id="chatMessages">
            @forelse($thread['messages'] as $msg)
                <div class="chat-msg chat-msg--{{ $msg['sender'] }}">
                    @if($msg['sender'] === 'shop')
                        <div class="chat-msg__avatar">C</div>
                    @endif
                    <div class="chat-msg__bubble @if(!empty($msg['image']) && empty($msg['content'])) chat-msg__bubble--image-only @endif">
                        @if(!empty($msg['image']))
                            <a href="{{ $msg['image'] }}" class="chat-msg__image" target="_blank" rel="noopener">
                                <img src="{{ $msg['image'] }}" alt="Ảnh đính kèm" loading="lazy">
                            </a>
                        @endif
                        @if(!empty($msg['content']))
                            <p>{{ $msg['content'] }}</p>
                        @endif
                        <span class="chat-msg__time">
                            {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i · d/m') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M8 10h32v22H20l-8 8v-8H8z" stroke-linejoin="round"/>
                    </svg>
                    <p>Chưa có tin nhắn. Gửi tin đầu tiên cho shop nhé!</p>
                </div>
            @endforelse
        </div>

        @if($errors->any())
            <div class="chat-form-errors">
                @foreach($errors->all() as $err)
                    <div>⚠ {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('user.chat.send') }}" class="chat-form" enctype="multipart/form-data" data-chat-form>
            @csrf
            <input type="hidden" name="thread_id" value="{{ $threadId }}">

            {{-- Preview ảnh sắp gửi --}}
            <div class="chat-form__preview" data-chat-preview hidden>
                <img src="" alt="" data-chat-preview-img>
                <button type="button" class="chat-form__preview-remove" data-chat-preview-remove aria-label="Bỏ ảnh">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="chat-form__row">
                {{-- File input — label kiểu icon button --}}
                <label class="chat-form__attach" aria-label="Đính kèm ảnh">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <circle cx="9" cy="11" r="2"/>
                        <path d="M21 17l-5-5-9 9"/>
                    </svg>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-chat-file hidden>
                </label>
                <textarea name="content" rows="2" maxlength="2000"
                          placeholder="Nhập tin nhắn hoặc đính kèm ảnh..."></textarea>
                <button type="submit" class="cart-btn cart-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 12l16-8-6 16-3-6-7-2z" stroke-linejoin="round"/>
                    </svg>
                    Gửi
                </button>
            </div>
        </form>
    </main>
</section>

<script>
(() => {
    const box = document.getElementById('chatMessages');
    if (box) box.scrollTop = box.scrollHeight;

    // Image preview & remove
    const form    = document.querySelector('[data-chat-form]');
    if (!form) return;
    const fileIn  = form.querySelector('[data-chat-file]');
    const preview = form.querySelector('[data-chat-preview]');
    const previewImg = form.querySelector('[data-chat-preview-img]');
    const removeBtn  = form.querySelector('[data-chat-preview-remove]');

    fileIn?.addEventListener('change', () => {
        const file = fileIn.files?.[0];
        if (!file) { preview.hidden = true; return; }
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            preview.hidden = false;
        };
        reader.readAsDataURL(file);
    });

    removeBtn?.addEventListener('click', () => {
        fileIn.value = '';
        previewImg.src = '';
        preview.hidden = true;
    });
})();
</script>
@endsection
