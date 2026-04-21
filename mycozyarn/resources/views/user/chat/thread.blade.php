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
                    <div class="chat-msg__bubble">
                        <p>{{ $msg['content'] }}</p>
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

        <form method="POST" action="{{ route('user.chat.send') }}" class="chat-form">
            @csrf
            <input type="hidden" name="thread_id" value="{{ $threadId }}">
            <textarea name="content" rows="2" required maxlength="2000"
                      placeholder="Nhập tin nhắn cho shop..."></textarea>
            <button type="submit" class="cart-btn cart-btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 12l16-8-6 16-3-6-7-2z" stroke-linejoin="round"/>
                </svg>
                Gửi
            </button>
        </form>
    </main>
</section>

<script>
(() => {
    const box = document.getElementById('chatMessages');
    if (box) box.scrollTop = box.scrollHeight;
})();
</script>
@endsection
