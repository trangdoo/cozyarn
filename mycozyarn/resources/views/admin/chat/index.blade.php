@extends('layouts.admin')

@section('title', 'Tin nhắn — CozyYarn')
@section('page_title', 'Tin nhắn khách hàng')

@php $active = 'chat'; @endphp

@section('content')
<div class="admin-page admin-page--chat">
    <div class="admin-chat">
        @include('admin.chat._threads-list', ['threads' => $threads, 'activeThreadId' => null])
        <main class="admin-chat__main admin-chat__main--empty">
            <div class="admin-chat__empty">
                <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 14h44v32H22l-12 10V14z"/></svg>
                <h2>Chọn một hội thoại</h2>
                <p>Chọn thread bên trái để xem và trả lời tin nhắn khách hàng.</p>
            </div>
        </main>
    </div>
</div>
@endsection
