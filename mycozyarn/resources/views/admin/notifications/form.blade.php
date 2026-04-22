@extends('layouts.admin')

@php
    $isEdit = !empty($notification);
    $action = $isEdit ? route('admin.notifications.update', $notification['id']) : route('admin.notifications.store');

    // Preload recipient mode (default: gửi cho tất cả user thường)
    $currentMode = 'role_user';
    $currentUsers = [];
    if ($isEdit) {
        $r = $notification['recipients'] ?? 'role:user';
        if ($r === 'role:admin') $currentMode = 'role_admin';
        elseif (\is_array($r)) { $currentMode = 'specific'; $currentUsers = array_map('intval', $r); }
        else $currentMode = 'role_user';
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

        @php
            $currentIcon = old('icon', $notification['icon'] ?? 'promo-discount');
            $iconOptions = [
                'Khuyến mãi' => [
                    ['key' => 'promo-discount', 'label' => 'Giảm giá',    'bg' => '#fff0e3', 'fg' => '#b15e1f',
                     'svg' => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" stroke-linejoin="round"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/>'],
                    ['key' => 'promo-ship',     'label' => 'Freeship',    'bg' => '#fde4ee', 'fg' => '#b55a82',
                     'svg' => '<rect x="3" y="8" width="13" height="9" rx="1"/><path d="M16 11h4l2 3v3h-6"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>'],
                    ['key' => 'promo-new',      'label' => 'Sản phẩm mới','bg' => '#e0f0ff', 'fg' => '#2c5580',
                     'svg' => '<path d="M12 2l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z" stroke-linejoin="round"/>'],
                ],
                'Đơn hàng' => [
                    ['key' => 'order-placed',    'label' => 'Đã đặt',      'bg' => '#fff6cc', 'fg' => '#9a7a1f',
                     'svg' => '<path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6M8 13h8M8 17h5" stroke-linecap="round"/>'],
                    ['key' => 'order-confirmed', 'label' => 'Đã xác nhận', 'bg' => '#c3e8d5', 'fg' => '#3d7a52',
                     'svg' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/>'],
                    ['key' => 'order-shipping',  'label' => 'Đang giao',   'bg' => '#fde4ee', 'fg' => '#b55a82',
                     'svg' => '<rect x="3" y="8" width="13" height="9" rx="1"/><path d="M16 11h4l2 3v3h-6"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>'],
                    ['key' => 'order-delivered', 'label' => 'Đã giao',     'bg' => '#d4efdb', 'fg' => '#2f6a42',
                     'svg' => '<path d="M20 7h-8l-2-3H4a1 1 0 0 0-1 1v15a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1z" stroke-linejoin="round"/><path d="M8 14l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/>'],
                ],
            ];
        @endphp

        <label style="margin-bottom:4px">Biểu tượng *</label>
        <div class="icon-picker">
            @foreach($iconOptions as $groupLabel => $icons)
                @php $groupType = $groupLabel === 'Khuyến mãi' ? 'promo' : 'order'; @endphp
                <div class="icon-picker__group" data-icon-group="{{ $groupType }}">
                    <span class="icon-picker__group-label">{{ $groupLabel }}</span>
                    <div class="icon-picker__grid">
                        @foreach($icons as $opt)
                            <label class="icon-pick">
                                <input type="radio" name="icon" value="{{ $opt['key'] }}" @checked($currentIcon === $opt['key'])>
                                <div class="icon-pick__inner">
                                    <span class="icon-pick__icon" style="background:{{ $opt['bg'] }};color:{{ $opt['fg'] }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            {!! $opt['svg'] !!}
                                        </svg>
                                    </span>
                                    <small>{{ $opt['label'] }}</small>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Các field chỉ có ý nghĩa với khuyến mãi --}}
        <div class="admin-form__row" data-promo-only>
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
                               data-search="{{ mb_strtolower("{$u->name} {$u->email}") }}">
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
    // ─── Type toggle: promo ↔ order ───
    // Ẩn nhóm icon không khớp + ẩn mã giảm giá/hiệu lực khi type=order
    const typeSel    = document.querySelector('select[name="type"]');
    const promoOnly  = document.querySelector('[data-promo-only]');
    const iconGroups = document.querySelectorAll('[data-icon-group]');
    const iconInputs = document.querySelectorAll('input[name="icon"]');

    const applyType = () => {
        const t = typeSel.value; // 'promo' | 'order'
        promoOnly.hidden = (t === 'order');
        iconGroups.forEach(g => {
            const match = g.dataset.iconGroup === t;
            g.hidden = !match;
        });
        // Nếu icon hiện tại không thuộc nhóm khớp → chọn icon đầu tiên của nhóm đó
        const checked = document.querySelector('input[name="icon"]:checked');
        const stillValid = checked && checked.closest(`[data-icon-group="${t}"]`);
        if (!stillValid) {
            const first = document.querySelector(`[data-icon-group="${t}"] input[name="icon"]`);
            if (first) first.checked = true;
        }
        // Clear code/valid_until khi chuyển sang order (để không submit data thừa)
        if (t === 'order') {
            promoOnly.querySelectorAll('input').forEach(i => i.value = '');
        }
    };
    typeSel?.addEventListener('change', applyType);
    applyType();

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
