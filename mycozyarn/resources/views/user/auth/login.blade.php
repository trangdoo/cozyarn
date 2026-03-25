@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Đăng nhập</h2>

    {{-- Hiển thị lỗi --}}
    @if ($errors->any())
        <div style="color:red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label>Mật khẩu</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
        </div>

        <button type="submit">Đăng nhập</button>
    </form>

    <p>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
</div>
@endsection