@extends('layouts.admin')

@php
    $isEdit = !empty($post);
    $action = $isEdit ? route('admin.blog.update', $post['slug']) : route('admin.blog.store');
    $currentBody = '';
    foreach (($post['sections'] ?? []) as $s) {
        $currentBody .= $s['body'] ?? '';
    }
@endphp

@section('title', ($isEdit ? 'Sửa' : 'Viết') . ' bài — CozyYarn')
@section('page_title', $isEdit ? 'Sửa bài viết' : 'Viết bài mới')

@php $active = 'blog'; @endphp

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.blog.index') }}" class="admin-back">← Danh sách</a>
            <h1>{{ $isEdit ? 'Sửa bài viết' : 'Viết bài mới' }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="admin-card admin-form" data-blog-form>
        @csrf
        @if($isEdit) @method('PATCH') @endif

        @if($errors->any())
            <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
        @endif

        <label>Tiêu đề *
            <input type="text" name="title" required autofocus value="{{ old('title', $post['title'] ?? '') }}">
        </label>

        <label>Tóm tắt * <small>(1-2 câu hiển thị ở card)</small>
            <textarea name="excerpt" rows="2" required maxlength="500">{{ old('excerpt', $post['excerpt'] ?? '') }}</textarea>
        </label>

        <label>Nội dung bài viết * <small>(soạn thảo có định dạng)</small>
            <div id="blogEditor" class="admin-quill"></div>
            <textarea name="body" hidden data-blog-body>{{ old('body', $currentBody) }}</textarea>
        </label>

        <div class="admin-form__row">
            <label>Danh mục *
                <select name="category" required>
                    @foreach($categories as $slug => $c)
                        <option value="{{ $slug }}" @selected(old('category', $post['category'] ?? '') === $slug)>{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>Thời gian đọc (phút) *
                <input type="number" name="read_time" required min="1" max="60" value="{{ old('read_time', $post['read_time'] ?? 5) }}">
            </label>
            <label>Tác giả
                <input type="text" name="author" value="{{ old('author', $post['author'] ?? auth()->user()->name) }}">
            </label>
        </div>

        <div class="admin-form__row">
            <label style="flex:2">Ảnh cover (URL)
                <input type="text" name="cover" value="{{ old('cover', $post['cover'] ?? '/images/1.jpg') }}">
            </label>
            <label>Ngày hiển thị
                <input type="date" name="date" value="{{ old('date', $post['date'] ?? now()->toDateString()) }}">
            </label>
        </div>

        {{-- ═══ Schedule ═══ --}}
        <div class="admin-schedule">
            <div class="admin-schedule__head">
                <strong>⏱ Lên lịch đăng bài</strong>
                <small>Bài sẽ chỉ hiển thị trên blog public sau thời điểm này</small>
            </div>
            <div class="admin-form__row">
                <label class="admin-checkbox">
                    <input type="checkbox" data-schedule-toggle
                           @checked(!empty($post['publish_at']) && $post['publish_at'] > now()->toDateTimeString())>
                    <span>Hẹn giờ đăng (mặc định: đăng ngay)</span>
                </label>
                <label data-schedule-input style="flex:2">Thời điểm đăng
                    <input type="datetime-local" name="publish_at"
                           value="{{ old('publish_at', !empty($post['publish_at']) ? \Carbon\Carbon::parse($post['publish_at'])->format('Y-m-d\\TH:i') : '') }}">
                </label>
            </div>
        </div>

        <label>Tags <small>(cách nhau dấu phẩy: len, cotton, hướng dẫn)</small>
            <input type="text" name="tags_raw" data-tags-input
                   value="{{ old('tags_raw', isset($post['tags']) ? implode(', ', $post['tags']) : '') }}">
            <div class="admin-tags-suggest" data-tags-suggest></div>
        </label>

        <label class="admin-checkbox">
            <input type="checkbox" name="featured" value="1" @checked(old('featured', $post['featured'] ?? false))>
            <span>Đặt làm bài nổi bật</span>
        </label>

        <div class="admin-form__actions">
            <a href="{{ route('admin.blog.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
            <button type="submit" class="admin-btn admin-btn--primary" data-save-btn>{{ $isEdit ? 'Lưu' : 'Đăng bài' }}</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(() => {
    // ─── Quill WYSIWYG editor ───
    const hiddenTa = document.querySelector('[data-blog-body]');
    const quill = new Quill('#blogEditor', {
        theme: 'snow',
        placeholder: 'Bắt đầu viết bài...',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                ['clean']
            ]
        }
    });
    if (hiddenTa.value) {
        quill.clipboard.dangerouslyPasteHTML(hiddenTa.value);
    }
    // Sync editor → hidden textarea mỗi khi đổi
    quill.on('text-change', () => {
        hiddenTa.value = quill.root.innerHTML;
    });

    // ─── Schedule toggle ───
    const schedCheck = document.querySelector('[data-schedule-toggle]');
    const schedInput = document.querySelector('[data-schedule-input]');
    const schedField = schedInput.querySelector('input[name="publish_at"]');
    const applySchedState = () => {
        if (schedCheck.checked) {
            schedInput.style.display = '';
            if (!schedField.value) {
                const d = new Date(Date.now() + 3600000); // +1h default
                schedField.value = d.toISOString().slice(0, 16);
            }
        } else {
            schedInput.style.display = 'none';
            schedField.value = '';
        }
    };
    schedCheck.addEventListener('change', applySchedState);
    applySchedState();

    // ─── Tag chip autocomplete (basic: show as chips) ───
    const tagIn = document.querySelector('[data-tags-input]');
    const suggestBox = document.querySelector('[data-tags-suggest]');
    const commonTags = ['hướng-dẫn', 'mẫu-đan', 'bảo-quản', 'cảm-hứng', 'cho-người-mới', 'len-cotton', 'len-mohair'];
    const renderSuggest = () => {
        suggestBox.innerHTML = '';
        const current = tagIn.value.split(',').map(s => s.trim()).filter(Boolean);
        commonTags.filter(t => !current.includes(t)).forEach(t => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'admin-tag-suggest';
            btn.textContent = '+ #' + t;
            btn.addEventListener('click', () => {
                const next = current.concat(t).join(', ');
                tagIn.value = next + (next.endsWith(',') ? ' ' : ', ');
                renderSuggest();
                tagIn.focus();
            });
            suggestBox.appendChild(btn);
        });
    };
    tagIn?.addEventListener('input', renderSuggest);
    renderSuggest();

    // ─── Shortcuts ───
    document.addEventListener('keydown', (e) => {
        if (e.altKey && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            hiddenTa.value = quill.root.innerHTML;
            document.querySelector('[data-save-btn]')?.click();
        } else if (e.key === 'Escape' && !e.target.closest('.ql-editor')) {
            window.location.href = @json(route('admin.blog.index'));
        }
    });

    // Ensure body syncs before submit
    document.querySelector('[data-blog-form]')?.addEventListener('submit', () => {
        hiddenTa.value = quill.root.innerHTML;
    });
})();
</script>
@endsection
