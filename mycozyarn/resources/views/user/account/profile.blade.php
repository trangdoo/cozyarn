@extends('layouts.public')

@section('title', 'Thông tin tài khoản — CozyYarn')

@section('content')
<section class="acc-page">
    <div class="acc-page__inner">
        <div class="acc-page__head">
            <span class="section-chip">Tài khoản</span>
            <h1 class="acc-page__title">Thông tin tài khoản</h1>
            <p class="acc-page__sub">Quản lý thông tin cá nhân và mật khẩu của bạn.</p>
        </div>

        <div class="acc-layout">
            @include('user.account._sidebar', ['active' => 'profile'])

            <div class="acc-content">
                {{-- Profile form --}}
                <section class="co-card">
                    <h3 class="co-card__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.7-3.4 5-5 8-5s6.3 1.6 8 5"/></svg>
                        Thông tin cá nhân
                    </h3>

                    @if($errors->any() && !$errors->has('current_password') && !$errors->has('new_password'))
                        <div class="cart-alert">
                            <ul style="margin:0;padding-left:18px">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="co-grid-2">
                            <label class="co-field">
                                <span class="co-label">Họ và tên <em>*</em></span>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            </label>
                            <label class="co-field">
                                <span class="co-label">Email</span>
                                <input type="email" value="{{ $user->email }}" disabled>
                                <small style="color:#a8748c;font-size:12px">Email không thể thay đổi.</small>
                            </label>
                        </div>

                        <label class="co-field">
                            <span class="co-label">Số điện thoại</span>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0912 345 678">
                        </label>

                        <label class="co-field">
                            <span class="co-label">Địa chỉ mặc định</span>
                            <textarea name="address" rows="3" placeholder="Số nhà, đường, phường, quận, tỉnh">{{ old('address', $user->address) }}</textarea>
                        </label>

                        <div class="acc-actions">
                            <button type="submit" class="cart-btn cart-btn--primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </section>

                {{-- Password form --}}
                <section class="co-card">
                    <h3 class="co-card__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Đổi mật khẩu
                    </h3>

                    @if($errors->has('current_password') || $errors->has('new_password'))
                        <div class="cart-alert">
                            {{ $errors->first('current_password') ?: $errors->first('new_password') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.password.update') }}">
                        @csrf
                        @method('PATCH')

                        <label class="co-field">
                            <span class="co-label">Mật khẩu hiện tại <em>*</em></span>
                            <input type="password" name="current_password" required>
                        </label>

                        <div class="co-grid-2">
                            <label class="co-field">
                                <span class="co-label">Mật khẩu mới <em>*</em></span>
                                <input type="password" name="new_password" required minlength="6">
                            </label>
                            <label class="co-field">
                                <span class="co-label">Nhập lại mật khẩu mới <em>*</em></span>
                                <input type="password" name="new_password_confirmation" required minlength="6">
                            </label>
                        </div>

                        <div class="acc-actions">
                            <button type="submit" class="cart-btn cart-btn--primary">Đổi mật khẩu</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
