@extends('layouts.admin')

@section('title', $thread['title'] . ' — Tin nhắn')
@section('page_title', 'Tin nhắn')

@php
    $active = 'chat';
    $isProduct = ($thread['type'] ?? '') === 'product';
@endphp

@section('content')
<div class="admin-page admin-page--chat">
    <div class="admin-chat">
        @include('admin.chat._threads-list', ['threads' => $threads, 'activeThreadId' => $activeThreadId])

        <main class="admin-chat__main">
            <header class="admin-chat__main-head">
                <div>
                    <strong>{{ $thread['title'] }}</strong>
                    <small>{{ $thread['subtitle'] ?? '' }}</small>
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
                            <span>{{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i · d/m') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty"><p>Thread rỗng.</p></div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.chat.reply', $threadId) }}" class="admin-chat__form">
                @csrf
                <textarea name="content" rows="2" required placeholder="Trả lời khách hàng..." maxlength="2000"></textarea>
                <button type="submit" class="admin-btn admin-btn--primary">Gửi</button>
            </form>
        </main>
    </div>
</div>

<script>
(() => { const b = document.getElementById('chatMessages'); if (b) b.scrollTop = b.scrollHeight; })();
</script>
@endsection
