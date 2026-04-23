@extends('layouts.public')

@section('title', 'Đặt lại mật khẩu — CozyYarn')

@push('head')
    @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="auth-page">
    <svg class="auth-decor auth-decor--1" viewBox="0 0 64 64" aria-hidden="true">
        <circle cx="32" cy="32" r="26" fill="#ffd2e2"/>
        <path d="M10 32 Q32 12 54 32 M10 32 Q32 52 54 32 M20 20 L44 44 M44 20 L20 44"
              stroke="#d97b9d" stroke-width="2" fill="none" stroke-linecap="round"/>
        <circle cx="54" cy="32" r="3" fill="#d97b9d"/>
    </svg>
    <svg class="auth-decor auth-decor--2" viewBox="0 0 48 48" aria-hidden="true">
        <path d="M24 42s-14-8-14-20a10 10 0 0 1 18-6 10 10 0 0 1 18 6c0 12-14 20-14 20z"
              transform="translate(-10 -2)" fill="#ff9ec0"/>
    </svg>
    <svg class="auth-decor auth-decor--3" viewBox="0 0 64 64" aria-hidden="true">
        <circle cx="32" cy="32" r="24" fill="#e9c8ff"/>
        <path d="M8 32 Q32 10 56 32 M8 32 Q32 54 56 32" stroke="#b586d6" stroke-width="2" fill="none"/>
    </svg>
    <svg class="auth-decor auth-decor--4" viewBox="0 0 48 48" aria-hidden="true">
        <path d="M24 3l5 14h14l-11 9 4 15-12-9-12 9 4-15L5 17h14z" fill="#ffd166"/>
    </svg>
    <svg class="auth-decor auth-decor--5" viewBox="0 0 48 48" aria-hidden="true">
        <path d="M24 4c-6 0-10 4-10 9 0 8 10 14 10 14s10-6 10-14c0-5-4-9-10-9z" fill="#ff85b0"/>
    </svg>
    <svg class="auth-decor auth-decor--6" viewBox="0 0 64 40" aria-hidden="true">
        <ellipse cx="20" cy="24" rx="14" ry="10" fill="#ffffff"/>
        <ellipse cx="36" cy="20" rx="16" ry="12" fill="#ffffff"/>
        <ellipse cx="50" cy="24" rx="12" ry="9"  fill="#ffffff"/>
    </svg>

    <div class="auth-card">
        <aside class="auth-side" aria-hidden="true">
            <span class="auth-side__shape auth-side__shape--1"></span>
            <span class="auth-side__shape auth-side__shape--2"></span>
            <span class="auth-side__shape auth-side__shape--3"></span>
            <span class="auth-side__shape auth-side__shape--4"></span>
            <span class="auth-side__shape auth-side__shape--5"></span>

            <svg class="auth-side__decor auth-side__decor--yarn" viewBox="0 0 64 64">
                <circle cx="32" cy="32" r="26" fill="#ffe4ef"/>
                <path d="M10 32 Q32 12 54 32 M10 32 Q32 52 54 32 M20 20 L44 44 M44 20 L20 44"
                      stroke="#d97b9d" stroke-width="2" fill="none" stroke-linecap="round"/>
                <path d="M54 32 q8 2 10 10" stroke="#fff" stroke-width="2" fill="none"/>
            </svg>
            <svg class="auth-side__decor auth-side__decor--heart" viewBox="0 0 48 48">
                <path d="M24 42s-14-8-14-20a9 9 0 0 1 17-4 9 9 0 0 1 17 4c0 12-14 20-14 20z"
                      transform="translate(-10 -2)" fill="#fff" opacity=".9"/>
            </svg>
            <svg class="auth-side__decor auth-side__decor--star" viewBox="0 0 48 48">
                <path d="M24 3l5 14h14l-11 9 4 15-12-9-12 9 4-15L5 17h14z" fill="#ffe6a8"/>
            </svg>

            <div class="auth-tabs">
                <a href="{{ route('login') }}" class="auth-tab auth-tab--ghost">LOGIN</a>
                <a href="{{ route('register') }}" class="auth-tab auth-tab--ghost">SIGN IN</a>
            </div>
        </aside>

        <div class="auth-form">
            <div class="auth-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                </svg>
            </div>
            <h2 class="auth-form__title">ĐẶT LẠI MẬT KHẨU</h2>
            <p class="auth-form__subtitle">Tài khoản: <strong>{{ $email }}</strong></p>

            @if ($errors->any())
                <div class="auth-alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.reset') }}">
                @csrf

                <div class="auth-field">
                    <span class="auth-field__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="password" placeholder="Mật khẩu mới" required autofocus minlength="6">
                </div>

                <div class="auth-field">
                    <span class="auth-field__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                            <path d="M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" required minlength="6">
                </div>

                <div class="auth-row">
                    <a href="{{ route('login') }}" class="auth-forgot">← Huỷ</a>
                    <button type="submit" class="auth-submit">ĐỔI MẬT KHẨU</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
