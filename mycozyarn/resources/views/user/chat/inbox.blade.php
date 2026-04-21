@extends('layouts.public')

@section('title', 'Tin nhắn — CozyYarn')

@section('content')
<section class="chat-app">
    @include('user.chat._threads-list', ['threads' => $threads, 'activeThreadId' => null])

    <main class="chat-app__main chat-app__main--empty">
        <div class="chat-app__empty">
            <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M10 14h44v32H22l-12 10V14z" stroke-linejoin="round"/>
                <circle cx="24" cy="30" r="2" fill="currentColor"/>
                <circle cx="32" cy="30" r="2" fill="currentColor"/>
                <circle cx="40" cy="30" r="2" fill="currentColor"/>
            </svg>
            <h2>Chọn một cuộc hội thoại</h2>
            <p>Bấm vào một hội thoại ở bên trái để xem tin nhắn, hoặc mở thread <strong>CozyYarn Shop</strong> để trò chuyện với shop.</p>
            <a href="{{ route('user.chat.thread', ['threadId' => 'shop']) }}" class="cart-btn cart-btn--primary">
                Nhắn cho shop ngay
            </a>
        </div>
    </main>
</section>
@endsection
