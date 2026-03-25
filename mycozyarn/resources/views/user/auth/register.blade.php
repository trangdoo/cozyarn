@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Đăng ký</h2>

    {{-- Hiển thị lỗi --}}
    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label>Tên</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label>Mật khẩu</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Nhập lại mật khẩu</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Đăng ký</button>
    </form>

    <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
</div>
@endsection