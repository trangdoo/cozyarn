@extends('layouts.admin')

@php
    $isEdit = !empty($notification);
    $action = $isEdit ? route('admin.notifications.update', $notification['id']) : route('admin.notifications.store');

    // Preload recipient mode
    $currentMode = 'all';
    $currentUsers = [];
    if ($isEdit) {
        $r = $notification['recipients'] ?? 'all';
        if ($r === 'all') $currentMode = 'all';
        elseif ($r === 'role:user')  $currentMode = 'role_user';
        elseif ($r === 'role:admin') $currentMode = 'role_admin';
        elseif (\is_array($r)) { $currentMode = 'specific'; $currentUsers = array_map('intval', $r); }
    }
    $meta = $notification['meta'] ?? [];
@endphp

@section('title', ($isEdit ? 'Sửa' : 'Soạn') . ' thông báo — CozyYarn')
@section('page_title', $isEdit ? 'Sửa thông báo (hẹn giờ)' : 'Soạn thông báo mới')

@php $active = 'notifications'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $isEdit ? 'Sửa thông báo đang hẹn giờ' : 'Soạn thông báo mới' }}</h1>
            <p>Gửi thông báo đến user: 1 người, nhiều người, toàn hệ thống, hoặc theo vai trò</p>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="admin-card admin-form" data-notif-form>
        @csrf
        @if($isEdit) @method('PATCH') @endif

        @if($errors->any())
            <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
        @endif

        {{-- ═══ Nội dung ═══ --}}
        <div class="admin-form__row">
            <label style="flex:2">Tiêu đề *
                <input type="text" name="title" required maxlength="200"
                       value="{{ old('title', $notification['title'] ?? '') }}"
                       placeholder="VD: 🎉 Giảm 30% toàn bộ len sợi">
            </label>
            <label>Loại *
                <select name="type" required>
                    <option value="promo" @selected(old('type', $notification['type'] ?? 'promo') === 'promo')>Khuyến mãi</option>
                    <option value="order" @selected(old('type', $notification['type'] ?? '') === 'order')>Thông báo đơn hàng</option>
                </select>
            </label>
        </div>

        <label>Nội dung *
            <textarea name="content" rows="3" required maxlength="500"
                      placeholder="Mô tả ngắn hiển thị ở list thông báo...">{{ old('content', $notification['content'] ?? '') }}</textarea>
        </label>

        <div class="admin-form__row">
            <label>Biểu tượng *
                <select name="icon" required>
                    <optgroup label="Khuyến mãi">
                        <option value="promo-discount" @selected(old('icon', $notification['icon'] ?? 'promo-discount') === 'promo-discount')>🎁 Giảm giá</option>
                        <option value="promo-ship" @selected(old('icon', $notification['icon'] ?? '') === 'promo-ship')>🚚 Freeship</option>
                        <option value="promo-new" @selected(old('icon', $notification['icon'] ?? '') === 'promo-new')>✨ Sản phẩm mới</option>
                    </optgroup>
                    <optgroup label="Đơn hàng">
                        <option value="order-placed" @selected(old('icon', $notification['icon'] ?? '') === 'order-placed')>📋 Đã đặt</option>
                        <option value="order-confirmed" @selected(old('icon', $notification['icon'] ?? '') === 'order-confirmed')>✅ Đã xác nhận</option>
                        <option value="order-shipping" @selected(old('icon', $notification['icon'] ?? '') === 'order-shipping')>🚛 Đang giao</option>
                        <option value="order-delivered" @selected(old('icon', $notification['icon'] ?? '') === 'order-delivered')>📦 Đã giao</option>
                    </optgroup>
                </select>
            </label>
            <label>Mã giảm giá
                <input type="text" name="code" maxlength="50" value="{{ old('code', $meta['code'] ?? '') }}" placeholder="VD: COZY30">
            </label>
            <label>Hiệu lực đến
                <input type="text" name="valid_until" maxlength="50" value="{{ old('valid_until', $meta['valid_until'] ?? '') }}" placeholder="VD: 30/04/2026">
            </label>
        </div>

        <label>Link (tuỳ chọn)
            <input type="text" name="link" maxlength="300" value="{{ old('link', $notification['link'] ?? '') }}" placeholder="VD: /shop/len-soi">
        </label>

        {{-- ═══ Recipients ═══ --}}
        <div class="admin-recip">
            <strong>📤 Gửi đến</strong>
            <div class="admin-recip__grid">
                <label class="admin-recip__option">
                    <input type="radio" name="recipient_mode" value="all" @checked(old('recipient_mode', $currentMode) === 'all')>
                    <span>
                        <strong>🌐 Toàn hệ thống</strong>
                        <small>Tất cả user có tài khoản</small>
                    </span>
                </label>
                <label class="admin-recip__option">
                    <input type="radio" name="recipient_mode" value="role_user" @checked(old('recipient_mode', $currentMode) === 'role_user')>
                    <span>
                        <strong>👥 Tất cả user</strong>
                        <small>Loại trừ admin</small>
                    </span>
                </label>
                <label class="admin-recip__option">
                    <input type="radio" name="recipient_mode" value="role_admin" @checked(old('recipient_mode', $currentMode) === 'role_admin')>
                    <span>
                        <strong>⚙ Chỉ admin</strong>
                        <small>Thông báo nội bộ</small>
                    </span>
                </label>
                <label class="admin-recip__option">
                    <input type="radio" name="recipient_mode" value="specific" @checked(old('recipient_mode', $currentMode) === 'specific')>
                    <span>
                        <strong>👤 Người cụ thể</strong>
                        <small>Chọn 1 hoặc nhiều user</small>
                    </span>
                </label>
            </div>

            <div class="admin-recip__users" data-recip-users>
                <div class="admin-recip__search">
                    <input type="search" placeholder="Tìm user theo tên/email..." data-recip-search>
                </div>
                <div class="admin-recip__list">
                    @foreach($users as $u)
                        <label class="admin-recip__user" data-user-row
                               data-search="{{ mb_strtolower($u->name . ' ' . $u->email) }}">
                            <input type="checkbox" name="recipient_users[]" value="{{ $u->id }}"
                                   @checked(\in_array($u->id, old('recipient_users', $currentUsers) ?? [], false))>
                            <div class="admin-recip__user-avatar">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                            <div class="admin-recip__user-info">
                                <strong>{{ $u->name }}</strong>
                                <small>{{ $u->email }} · {{ $u->role }}</small>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="admin-recip__selected">
                    Đã chọn <strong data-recip-count>0</strong> người dùng
                </div>
            </div>
        </div>

        {{-- ═══ Schedule ═══ --}}
        <div class="admin-schedule">
            <div class="admin-schedule__head">
                <strong>⏱ Hẹn giờ gửi (tuỳ chọn)</strong>
                <small>Bỏ trống = gửi ngay</small>
            </div>
            <div class="admin-form__row">
                <label class="admin-checkbox">
                    <input type="checkbox" data-schedule-toggle
                           @checked($isEdit && !empty($notification['send_at']) && $notification['send_at'] > now()->toDateTimeString())>
                    <span>Lên lịch gửi vào thời điểm cụ thể</span>
                </label>
                <label data-schedule-input style="flex:2">Thời điểm gửi
                    <input type="datetime-local" name="send_at"
                           value="{{ old('send_at', !empty($notification['send_at']) ? \Carbon\Carbon::parse($notification['send_at'])->format('Y-m-d\\TH:i') : '') }}">
                </label>
            </div>
        </div>

        <div class="admin-form__actions">
            <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary">{{ $isEdit ? 'Lưu thay đổi' : 'Gửi / Lên lịch' }}</button>
        </div>
    </form>
</div>

<script>
(() => {
    // ─── Recipient mode toggle ───
    const recipUsers = document.querySelector('[data-recip-users]');
    const modeRadios = document.querySelectorAll('input[name="recipient_mode"]');
    const applyMode = () => {
        const mode = document.querySelector('input[name="recipient_mode"]:checked')?.value;
        recipUsers.hidden = mode !== 'specific';
    };
    modeRadios.forEach(r => r.addEventListener('change', applyMode));
    applyMode();

    // ─── Search user list ───
    const searchIn = document.querySelector('[data-recip-search]');
    const userRows = document.querySelectorAll('[data-user-row]');
    searchIn?.addEventListener('input', () => {
        const q = searchIn.value.trim().toLowerCase();
        userRows.forEach(r => {
            r.style.display = !q || r.dataset.search.includes(q) ? '' : 'none';
        });
    });

    // ─── Count selected users ───
    const countEl = document.querySelector('[data-recip-count]');
    const refreshCount = () => {
        countEl.textContent = document.querySelectorAll('input[name="recipient_users[]"]:checked').length;
    };
    document.querySelectorAll('input[name="recipient_users[]"]').forEach(c => c.addEventListener('change', refreshCount));
    refreshCount();

    // ─── Schedule toggle ───
    const schedCheck = document.querySelector('[data-schedule-toggle]');
    const schedInput = document.querySelector('[data-schedule-input]');
    const schedField = schedInput.querySelector('input[name="send_at"]');
    const applySched = () => {
        if (schedCheck.checked) {
            schedInput.style.display = '';
            if (!schedField.value) {
                const d = new Date(Date.now() + 3600000);
                schedField.value = d.toISOString().slice(0, 16);
            }
        } else {
            schedInput.style.display = 'none';
            schedField.value = '';
        }
    };
    schedCheck.addEventListener('change', applySched);
    applySched();
})();
</script>
@endsection
