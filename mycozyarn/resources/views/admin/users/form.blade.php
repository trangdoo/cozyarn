@extends('layouts.admin')

@section('title', 'Thêm tài khoản — CozyYarn')
@section('page_title', 'Thêm tài khoản')

@php $active = 'users'; @endphp

@push('head')
    @vite(['resources/js/auth-validate.js'])
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.users.index') }}" class="admin-back">← Về danh sách</a>
            <h1>Thêm tài khoản mới</h1>
            <p>Tạo trực tiếp một tài khoản người dùng hoặc quản trị viên.</p>
        </div>
    </div>

    <div class="admin-card">
        <form method="POST" action="{{ route('admin.users.store') }}" class="admin-form" data-validate>
            @csrf

            @if($errors->any())
                <div class="admin-errors">
                    @foreach($errors->all() as $e)
                        <div>⚠ {{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="admin-form__row">
                <label>Họ tên <span style="color:#a63652">*</span>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                           data-rule="name" data-required>
                </label>
                <label>Email <span style="color:#a63652">*</span>
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="100"
                           data-rule="email" data-required>
                </label>
            </div>

            <div class="admin-form__row">
                <label>Mật khẩu <span style="color:#a63652">*</span>
                    <input type="password" name="password" required minlength="6" maxlength="100" autocomplete="new-password"
                           data-rule="password" data-required data-hash>
                </label>
                <label>Xác nhận mật khẩu <span style="color:#a63652">*</span>
                    <input type="password" name="password_confirmation" required minlength="6" maxlength="100" autocomplete="new-password"
                           data-match="password" data-required data-hash>
                </label>
            </div>

            <div class="admin-form__row">
                <label>Số điện thoại
                    <input type="text" name="phone" value="{{ old('phone') }}" maxlength="20" placeholder="VD: 0901234567"
                           data-rule="phone">
                </label>
                <label>Vai trò <span style="color:#a63652">*</span>
                    <select name="role" required>
                        <option value="user" @selected(old('role', 'user') === 'user')>User</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                </label>
                <label>Trạng thái <span style="color:#a63652">*</span>
                    <select name="status" required>
                        <option value="active" @selected(old('status', 'active') === 'active')>Hoạt động</option>
                        <option value="blocked" @selected(old('status') === 'blocked')>Đã khoá</option>
                    </select>
                </label>
            </div>

            <label>Địa chỉ
                <textarea name="address" rows="2" maxlength="300">{{ old('address') }}</textarea>
            </label>

            <div class="admin-form__actions">
                <button type="submit" class="admin-btn admin-btn--primary">Tạo tài khoản</button>
                <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
