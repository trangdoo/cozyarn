@extends('layouts.admin')

@section('title', 'Chi tiết tài khoản — CozyYarn')
@section('page_title', $user->name)

@php $active = 'users'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.users.index') }}" class="admin-back">← Về danh sách</a>
            <h1>{{ $user->name }}</h1>
            <p>Chi tiết và chỉnh sửa tài khoản</p>
        </div>
    </div>

    <div class="admin-grid-2">
        <section class="admin-card">
            <header class="admin-card__head"><h2>Hồ sơ</h2></header>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form">
                @csrf @method('PATCH')

                @if($errors->any())
                    <div class="admin-errors">
                        @foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach
                    </div>
                @endif

                <label>Họ tên
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </label>
                <label>Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </label>
                <label>Số điện thoại
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                </label>
                <label>Địa chỉ
                    <textarea name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                </label>

                <div class="admin-form__row">
                    <label>Vai trò
                        <select name="role" @if($user->id === auth()->id()) disabled @endif>
                            <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        </select>
                        @if($user->id === auth()->id())<small>Không đổi vai trò của chính mình</small>@endif
                    </label>
                    <label>Trạng thái
                        <select name="status">
                            <option value="active" @selected(old('status', $user->status) === 'active')>Hoạt động</option>
                            <option value="blocked" @selected(old('status', $user->status) === 'blocked')>Đã khoá</option>
                        </select>
                    </label>
                </div>

                @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                @endif

                <div class="admin-form__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Lưu thay đổi</button>
                    @if($user->id !== auth()->id())
                        <button type="submit"
                                formaction="{{ route('admin.users.destroy', $user) }}"
                                formmethod="POST"
                                class="admin-btn admin-btn--danger"
                                onclick="return confirm('Xoá vĩnh viễn tài khoản này?');">
                            Xoá tài khoản
                        </button>
                        <input type="hidden" name="_method" value="DELETE" form="delete-user-form">
                    @endif
                </div>
            </form>
            @if($user->id !== auth()->id())
                <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:none">
                    @csrf @method('DELETE')
                </form>
            @endif
        </section>

        <section class="admin-card">
            <header class="admin-card__head"><h2>Thông tin thêm</h2></header>
            <ul class="admin-info">
                <li><span>ID</span><strong>#{{ $user->id }}</strong></li>
                <li><span>Ngày tạo</span><strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong></li>
                <li><span>Cập nhật cuối</span><strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong></li>
                <li><span>Email verified</span><strong>{{ $user->email_verified_at ? '✓ Đã xác thực' : '—' }}</strong></li>
            </ul>
        </section>
    </div>
</div>
@endsection
