@extends('layouts.public')

@section('title', 'Quét QR để thanh toán — CozyYarn')

@push('head')
<style>
.pay-page{padding:48px 16px 64px;background:linear-gradient(180deg,#fff5f8 0%,#fff 60%)}
.pay-page__inner{max-width:880px;margin:0 auto}
.pay-hero{text-align:center;margin-bottom:24px}
.pay-hero__chip{display:inline-block;padding:6px 14px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:12px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;margin-bottom:10px}
.pay-hero__title{font-size:30px;line-height:1.2;margin:0 0 6px;color:#1f2937}
.pay-hero__sub{margin:0;color:#475569;font-size:14px;line-height:1.6}
.pay-steps{display:flex;justify-content:center;gap:8px;margin-top:14px;flex-wrap:wrap}
.pay-step{font-size:12px;padding:4px 12px;border-radius:999px;background:#f1f5f9;color:#64748b}
.pay-step.is-done{background:#dcfce7;color:#166534}
.pay-step.is-active{background:#fde68a;color:#92400e;font-weight:600}
.pay-card{background:#fff;border-radius:18px;box-shadow:0 8px 32px rgba(15,23,42,.08);padding:28px;display:grid;grid-template-columns:300px 1fr;gap:32px;align-items:start;margin-top:8px}
@media (max-width:720px){.pay-card{grid-template-columns:1fr;gap:20px;padding:20px}}
.pay-qr{text-align:center}
.pay-qr__img{width:100%;max-width:300px;border-radius:14px;border:6px solid #fff;box-shadow:0 4px 14px rgba(15,23,42,.12);background:#fff;aspect-ratio:1/1;object-fit:contain}
.pay-qr__cap{display:block;margin-top:10px;font-size:13px;color:#64748b}
.pay-status{margin-top:14px;padding:12px;border-radius:12px;background:#fef3c7;color:#92400e;display:flex;align-items:center;justify-content:center;gap:10px;font-weight:600;font-size:14px}
.pay-status.is-paid{background:#dcfce7;color:#166534}
.pay-status__spinner{width:14px;height:14px;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.pay-info{list-style:none;padding:0;margin:0;display:grid;gap:10px}
.pay-info li{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#f8fafc;border-radius:10px;font-size:14px;gap:12px}
.pay-info li > span:first-child{color:#64748b}
.pay-info li > strong{color:#0f172a;text-align:right;word-break:break-all}
.pay-info li.is-memo strong{color:#b91c1c;font-family:monospace;letter-spacing:1px}
.pay-info li.is-amount strong{color:#1e3a8a;font-size:18px}
.pay-copy{margin-left:6px;background:none;border:1px solid #cbd5e1;border-radius:6px;padding:2px 8px;font-size:11px;color:#475569;cursor:pointer}
.pay-copy:hover{background:#f1f5f9}
.pay-note{margin-top:16px;padding:14px;background:#eff6ff;border-radius:10px;font-size:13px;color:#1e3a8a;line-height:1.6}
.pay-note strong{color:#1d4ed8}
.pay-actions{margin-top:22px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.pay-btn{padding:11px 22px;border-radius:10px;font-size:14px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:.2s}
.pay-btn--primary{background:#d97b9d;color:#fff}
.pay-btn--primary:hover{background:#c66687}
.pay-btn--ghost{background:#f1f5f9;color:#475569}
.pay-btn--ghost:hover{background:#e2e8f0}
</style>
@endpush

@section('content')
<section class="pay-page">
    <div class="pay-page__inner">
        <div class="pay-hero">
            <span class="pay-hero__chip">Đang chờ thanh toán</span>
            <h1 class="pay-hero__title">Quét QR để hoàn tất giao dịch</h1>
            <p class="pay-hero__sub">
                Đơn <strong>#{{ $order['id'] }}</strong> đã được tạo. Quét mã VietQR bên dưới bằng app ngân hàng —<br>
                hệ thống sẽ tự xác nhận trong vài giây sau khi nhận được tiền.
            </p>
            <div class="pay-steps">
                <span class="pay-step is-done">1. Giỏ hàng</span>
                <span class="pay-step is-done">2. Thông tin</span>
                <span class="pay-step is-active">3. Quét QR</span>
                <span class="pay-step">4. Hoàn tất</span>
            </div>
        </div>

        <div class="pay-card">
            <div class="pay-qr">
                <img class="pay-qr__img" src="{{ $bank['qr_url'] }}" alt="VietQR thanh toán đơn {{ $order['id'] }}" loading="eager">
                <span class="pay-qr__cap">Quét bằng app ngân hàng bất kỳ</span>
                <div class="pay-status" data-pay-status>
                    <span class="pay-status__spinner" aria-hidden="true"></span>
                    <span data-pay-status-text>Đang chờ ngân hàng xác nhận...</span>
                </div>
            </div>

            <div>
                <ul class="pay-info">
                    <li>
                        <span>Ngân hàng</span>
                        <strong>{{ $bank['bank_name'] ?: $bank['bank'] }}</strong>
                    </li>
                    <li>
                        <span>Số tài khoản</span>
                        <strong>
                            {{ $bank['account_number'] }}
                            <button type="button" class="pay-copy" data-copy="{{ $bank['account_number'] }}">Sao chép</button>
                        </strong>
                    </li>
                    @if(!empty($bank['account_name']))
                        <li>
                            <span>Chủ tài khoản</span>
                            <strong>{{ $bank['account_name'] }}</strong>
                        </li>
                    @endif
                    <li class="is-amount">
                        <span>Số tiền</span>
                        <strong>{{ number_format($bank['amount'], 0, ',', '.') }} ₫</strong>
                    </li>
                    <li class="is-memo">
                        <span>Nội dung CK</span>
                        <strong>
                            {{ $bank['memo'] }}
                            <button type="button" class="pay-copy" data-copy="{{ $bank['memo'] }}">Sao chép</button>
                        </strong>
                    </li>
                </ul>

                <div class="pay-note">
                    <strong>Lưu ý:</strong> Khi chuyển khoản thủ công, nội dung phải chính xác là
                    <strong>{{ $bank['memo'] }}</strong> để hệ thống tự đối soát. Nếu quét QR thì
                    nội dung đã được điền sẵn — chỉ cần xác nhận.
                </div>

                <div class="pay-actions">
                    <a class="pay-btn pay-btn--ghost" href="{{ route('user.orders.show', ['id' => $order['id']]) }}">Xem đơn hàng</a>
                    <a class="pay-btn pay-btn--primary" href="{{ route('shop.index') }}">Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(() => {
    // ─── Sao chép số TK / nội dung CK ───
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const text = btn.dataset.copy || '';
            try {
                await navigator.clipboard.writeText(text);
                const orig = btn.textContent;
                btn.textContent = 'Đã chép ✓';
                setTimeout(() => { btn.textContent = orig; }, 1500);
            } catch (e) {
                btn.textContent = 'Hãy chép tay';
            }
        });
    });

    // ─── Poll trạng thái thanh toán mỗi 4s ───
    // Khi webhook SePay flip payment_status='paid', endpoint trả redirect URL → tự chuyển trang.
    const statusEl = document.querySelector('[data-pay-status]');
    const statusText = document.querySelector('[data-pay-status-text]');
    const url = @json(route('checkout.payStatus', ['id' => $order['id']]));

    let stopped = false;
    async function poll() {
        if (stopped) return;
        try {
            const r = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!r.ok) throw new Error('http ' + r.status);
            const data = await r.json();
            if (data.payment_status === 'paid' && data.redirect) {
                stopped = true;
                if (statusEl) statusEl.classList.add('is-paid');
                if (statusText) statusText.textContent = 'Đã nhận thanh toán ✓ — đang chuyển...';
                const spinner = document.querySelector('.pay-status__spinner');
                if (spinner) spinner.style.display = 'none';
                setTimeout(() => { window.location.href = data.redirect; }, 800);
                return;
            }
        } catch (e) {
            // Lỗi mạng → bỏ qua, thử lại lần sau.
        }
        setTimeout(poll, 4000);
    }
    setTimeout(poll, 2000);
})();
</script>
@endsection
