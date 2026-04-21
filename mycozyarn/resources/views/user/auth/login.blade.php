@extends('layouts.public')

@section('title', 'Đăng nhập — CozyYarn')

@push('head')
    @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="auth-page">
    {{-- floating cute decorations on page background --}}
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
        {{-- LEFT: decorative panel --}}
        <aside class="auth-side" aria-hidden="true">
            <span class="auth-side__shape auth-side__shape--1"></span>
            <span class="auth-side__shape auth-side__shape--2"></span>
            <span class="auth-side__shape auth-side__shape--3"></span>
            <span class="auth-side__shape auth-side__shape--4"></span>
            <span class="auth-side__shape auth-side__shape--5"></span>

            {{-- cute mascots on the pink panel --}}
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
                <a href="{{ route('login') }}" class="auth-tab">LOGIN</a>
                <a href="{{ route('register') }}" class="auth-tab auth-tab--ghost">SIGN IN</a>
            </div>
        </aside>

        {{-- RIGHT: form --}}
        <div class="auth-form">
            <div class="auth-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/>
                </svg>
            </div>
            <h2 class="auth-form__title">LOGIN</h2>

            @if ($errors->any())
                <div class="auth-alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="auth-field">
                    <span class="auth-field__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 7l9 6 9-6"/>
                        </svg>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
                </div>

                <div class="auth-field">
                    <span class="auth-field__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                    </span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <label class="auth-remember">
                    <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                </label>

                <div class="auth-row">
                    <a href="#" class="auth-forgot">Forgot Password?</a>
                    <button type="submit" class="auth-submit">LOGIN</button>
                </div>
            </form>

            <p class="auth-swap">
                Chưa có tài khoản?<a href="{{ route('register') }}">Đăng ký ngay</a>
            </p>

            <div class="auth-social">
                <span class="auth-social__label">Or Login With</span>
                <div class="auth-social__btns">
                    <a href="#" class="auth-social__btn" aria-label="Login with Google">
                        <svg viewBox="0 0 48 48">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59A14.5 14.5 0 0 1 9.5 24c0-1.59.27-3.14.74-4.59l-7.98-6.19A23.97 23.97 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            <path fill="none" d="M0 0h48v48H0z"/>
                        </svg>
                        Google
                    </a>
                    <a href="#" class="auth-social__btn" aria-label="Login with Facebook">
                        <svg viewBox="0 0 24 24">
                            <path fill="#1877F2" d="M24 12a12 12 0 1 0-13.875 11.854V15.47H7.078V12h3.047V9.356c0-3.007 1.792-4.668 4.533-4.668 1.312 0 2.686.234 2.686.234v2.953h-1.513c-1.49 0-1.955.925-1.955 1.875V12h3.328l-.532 3.47h-2.796v8.384A12.003 12.003 0 0 0 24 12z"/>
                        </svg>
                        Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
