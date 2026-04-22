@extends('layouts.admin')

@section('title', 'Gửi thông báo — CozyYarn')
@section('page_title', 'Gửi thông báo khuyến mãi')

@php $active = 'notifications'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="admin-back">← Danh sách</a>
            <h1>Gửi khuyến mãi mới</h1>
            <p>Thông báo sẽ hiển thị trên chuông header + trang /thong-bao của user</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.notifications.store') }}" class="admin-card admin-form">
        @csrf

        @if($errors->any())
            <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
        @endif

        <label>Tiêu đề *
            <input type="text" name="title" required maxlength="200" placeholder="VD: 🎉 Giảm 30% toàn bộ len sợi">
        </label>

        <label>Nội dung ngắn *
            <textarea name="content" rows="3" required maxlength="500" placeholder="Mô tả ngắn hiển thị trong list thông báo..."></textarea>
        </label>

        <div class="admin-form__row">
            <label>Biểu tượng *
                <select name="icon" required>
                    <option value="promo-discount">Giảm giá</option>
                    <option value="promo-ship">Freeship</option>
                    <option value="promo-new">Sản phẩm mới</option>
                </select>
            </label>
            <label>Mã giảm giá (tuỳ chọn)
                <input type="text" name="code" maxlength="50" placeholder="VD: COZY30">
            </label>
            <label>Hiệu lực
                <input type="text" name="valid_until" maxlength="50" placeholder="VD: 30/04/2026">
            </label>
        </div>

        <label>Link (tuỳ chọn)
            <input type="text" name="link" maxlength="300" placeholder="VD: /shop/len-soi">
        </label>

        <div class="admin-form__actions">
            <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary">Gửi thông báo</button>
        </div>
    </form>
</div>
@endsection
