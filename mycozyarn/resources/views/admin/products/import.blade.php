@extends('layouts.admin')

@section('title', 'Nhập sản phẩm — CozyYarn')
@section('page_title', 'Nhập sản phẩm từ file')

@php $active = 'products'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <a href="{{ route('admin.products.index') }}" class="admin-back">← Danh sách</a>
            <h1>Nhập dữ liệu sản phẩm</h1>
            <p>Hỗ trợ file CSV (Excel), JSON, XML — tối đa 10MB</p>
        </div>
    </div>

    <div class="admin-grid-2">
        <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="admin-card admin-form">
            @csrf

            @if($errors->any())
                <div class="admin-errors">@foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach</div>
            @endif

            <div class="admin-dropzone" data-dropzone>
                <input type="file" name="file" accept=".csv,.txt,.json,.xml" required hidden data-file-input>
                <div class="admin-dropzone__empty">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M24 6v28m0-28l-8 8m8-8l8 8M8 36v4a2 2 0 0 0 2 2h28a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <strong>Bấm hoặc kéo thả file vào đây</strong>
                    <small>.csv · .json · .xml</small>
                </div>
                <div class="admin-dropzone__file" hidden data-file-info>
                    <strong data-file-name></strong>
                    <small data-file-size></small>
                    <button type="button" class="admin-btn admin-btn--ghost" data-file-reset>Đổi file</button>
                </div>
            </div>

            <div class="admin-form__actions">
                <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn--ghost">Huỷ</a>
                <button type="submit" class="admin-btn admin-btn--primary">Nhập dữ liệu</button>
            </div>
        </form>

        <section class="admin-card">
            <header class="admin-card__head"><h2>Định dạng file</h2></header>
            <div class="admin-show__desc">
                <p><strong>Các cột hỗ trợ</strong> (tương ứng field ProductID, ProductName, ProductType...):</p>
                <ul>
                    <li><code>id</code> — ProductID (slug, để trống = auto-generate)</li>
                    <li><code>name</code> — ProductName *</li>
                    <li><code>category</code> — ProductType (slug danh mục)</li>
                    <li><code>image</code> — PathImage (URL ảnh)</li>
                    <li><code>unit</code> — Đơn vị (cuộn / cái / bộ)</li>
                    <li><code>quantity</code> — Số lượng tồn</li>
                    <li><code>price</code> — Giá bán (VND)</li>
                    <li><code>description</code> — Mô tả ngắn</li>
                    <li><code>status</code> — active / inactive</li>
                </ul>
                <p><strong>Ví dụ CSV:</strong></p>
                <pre class="admin-code-block">id,name,category,image,unit,quantity,price,description,status
,len-cotton-mau-hong,len-soi,/images/1.jpg,cuộn,50,95000,Len cotton mềm mịn,active
,kim-moc-8mm,kim-moc,/images/2.jpg,cái,30,45000,Kim móc inox 8mm,active</pre>
                <p><small>💡 Mẹo: xuất file trước (Alt+E) để lấy đúng format, chỉnh sửa, rồi nhập lại.</small></p>
            </div>
        </section>
    </div>
</div>

<script>
(() => {
    const zone  = document.querySelector('[data-dropzone]');
    const input = zone.querySelector('[data-file-input]');
    const empty = zone.querySelector('.admin-dropzone__empty');
    const info  = zone.querySelector('[data-file-info]');
    const nameEl = zone.querySelector('[data-file-name]');
    const sizeEl = zone.querySelector('[data-file-size]');

    const showFile = (file) => {
        empty.hidden = true;
        info.hidden  = false;
        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
    };
    const resetFile = () => {
        input.value = '';
        empty.hidden = false;
        info.hidden  = true;
    };

    zone.addEventListener('click', (e) => {
        if (e.target.closest('[data-file-reset]')) return;
        input.click();
    });
    input.addEventListener('change', () => input.files[0] && showFile(input.files[0]));
    zone.querySelector('[data-file-reset]').addEventListener('click', resetFile);

    // Drag & drop
    ['dragenter', 'dragover'].forEach(evt => zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.add('is-drag'); }));
    ['dragleave', 'drop'].forEach(evt => zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.remove('is-drag'); }));
    zone.addEventListener('drop', (e) => {
        const file = e.dataTransfer.files[0];
        if (!file) return;
        input.files = e.dataTransfer.files;
        showFile(file);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') window.location = @json(route('admin.products.index'));
    });
})();
</script>
@endsection
