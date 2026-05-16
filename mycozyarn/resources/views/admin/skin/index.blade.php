@extends('layouts.admin')

@section('title', 'Giao diện (Skin) — Quản trị')

@php $active = 'skin'; @endphp

@section('content')
<section class="admin-content__inner" style="padding:24px;max-width:1100px;margin:0 auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <div>
            <h1 style="margin:0 0 6px;font-size:24px">Tùy biến giao diện (Skin)</h1>
            <p style="margin:0;color:#6c5b66;font-size:14px">
                Đổi skin của shop ngay lập tức. Theme đang chọn sẽ load thêm 1 file CSS đè lên theme mặc định.
            </p>
        </div>
        <span class="section-chip">Skin hiện tại: <strong>{{ $themes[$activeTheme]['name'] }}</strong></span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px">
        @foreach($themes as $key => $meta)
            @php $isActive = $key === $activeTheme; @endphp
            <form method="POST" action="{{ route('admin.skin.update') }}" class="co-card" style="margin:0;padding:0;overflow:hidden;{{ $isActive ? 'box-shadow:0 0 0 2px '.$meta['accent'] : '' }}">
                @csrf

                <div style="height:120px;background:linear-gradient(135deg,{{ $meta['preview'] }} 0%,{{ $meta['accent'] }} 100%);position:relative">
                    @if($isActive)
                        <span style="position:absolute;top:10px;right:10px;background:#fff;color:{{ $meta['accent'] }};padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700">✓ Đang dùng</span>
                    @endif
                </div>

                <div style="padding:16px">
                    <h3 style="margin:0 0 6px;font-size:17px">{{ $meta['name'] }}</h3>
                    <p style="margin:0 0 12px;color:#6c5b66;font-size:13px;line-height:1.5;min-height:38px">{{ $meta['desc'] }}</p>

                    <div style="display:flex;gap:6px;margin-bottom:14px">
                        <span style="width:24px;height:24px;border-radius:50%;background:{{ $meta['preview'] }};border:2px solid #fff;box-shadow:0 0 0 1px #ddd"></span>
                        <span style="width:24px;height:24px;border-radius:50%;background:{{ $meta['accent'] }};border:2px solid #fff;box-shadow:0 0 0 1px #ddd"></span>
                        <code style="margin-left:auto;font-size:11px;color:#999;align-self:center">key: {{ $key }}</code>
                    </div>

                    <input type="hidden" name="theme" value="{{ $key }}">
                    <button type="submit"
                            class="cart-btn {{ $isActive ? 'cart-btn--ghost' : 'cart-btn--primary' }}"
                            style="width:100%"
                            @disabled($isActive)>
                        {{ $isActive ? 'Đang áp dụng' : 'Áp dụng skin này' }}
                    </button>
                </div>
            </form>
        @endforeach
    </div>

    <div class="co-card" style="margin-top:24px;padding:18px">
        <h3 style="margin:0 0 10px;font-size:15px">📁 Tạo skin mới</h3>
        <p style="margin:0 0 8px;color:#6c5b66;font-size:13px;line-height:1.6">
            1. Tạo file <code>public/themes/{key}.css</code> với các selector override.<br>
            2. Đăng ký metadata vào hằng <code>THEMES</code> trong <code>app/Support/ThemeManager.php</code>.<br>
            3. Refresh trang này — skin mới sẽ hiển thị trong danh sách.
        </p>
    </div>
</section>
@endsection
