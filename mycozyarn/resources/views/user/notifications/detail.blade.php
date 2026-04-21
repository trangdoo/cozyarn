@extends('layouts.public')

@section('title', $notification['title'] . ' — CozyYarn')

@php
    $meta       = $notification['meta'] ?? [];
    $details    = $meta['details']     ?? [];
    $highlights = $meta['highlights']  ?? [];
    $cta        = $meta['cta']         ?? 'Xem ngay';
    $banner     = $meta['banner']      ?? null;
    $code       = $meta['code']        ?? null;
    $validUntil = $meta['valid_until'] ?? null;
    $link       = $notification['link'] ?? null;
@endphp

@section('content')
<section class="promo-detail">
    <div class="promo-detail__inner">
        <nav class="policy-breadcrumb" aria-label="breadcrumb">
            <a href="/">Trang chủ</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('user.notifications.index') }}">Thông báo</a>
            <span aria-hidden="true">›</span>
            <span class="policy-breadcrumb__current">Chi tiết khuyến mãi</span>
        </nav>

        @if($banner)
            <div class="promo-detail__banner">
                <img src="{{ $banner }}" alt="{{ $notification['title'] }}">
                <div class="promo-detail__banner-overlay">
                    <span class="promo-detail__chip">Khuyến mãi</span>
                    <h1 class="promo-detail__title">{{ $notification['title'] }}</h1>
                    <p class="promo-detail__lead">{{ $notification['content'] }}</p>
                </div>
            </div>
        @else
            <header class="promo-detail__head">
                <span class="promo-detail__chip">Khuyến mãi</span>
                <h1 class="promo-detail__title">{{ $notification['title'] }}</h1>
                <p class="promo-detail__lead">{{ $notification['content'] }}</p>
            </header>
        @endif

        <div class="promo-detail__grid">
            <article class="promo-detail__content">
                @if(!empty($details))
                    @foreach($details as $paragraph)
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endforeach
                @else
                    <p>{{ $notification['content'] }}</p>
                @endif

                @if(!empty($highlights))
                    <h3 class="promo-detail__subhead">
                        <span aria-hidden="true">✨</span> Điểm nổi bật
                    </h3>
                    <ul class="promo-detail__highlights">
                        @foreach($highlights as $h)
                            <li>
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="currentColor" opacity="0.15"/><path d="M6 10l3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>{{ $h }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>

            <aside class="promo-detail__sidebar">
                @if($code)
                    <div class="promo-detail__code">
                        <small>Mã khuyến mãi</small>
                        <div class="promo-detail__code-box">
                            <strong id="promoCode">{{ $code }}</strong>
                            <button type="button" class="promo-detail__copy" data-copy="{{ $code }}" aria-label="Sao chép mã">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="8" y="8" width="12" height="12" rx="2"/>
                                    <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>
                                </svg>
                                <span data-copy-label>Sao chép</span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="promo-detail__info">
                    <div class="promo-detail__info-row">
                        <span>Hiệu lực</span>
                        <strong>{{ $validUntil ?? 'Không giới hạn' }}</strong>
                    </div>
                    <div class="promo-detail__info-row">
                        <span>Nhận thông báo</span>
                        <strong>{{ \Carbon\Carbon::parse($notification['created_at'])->format('H:i · d/m/Y') }}</strong>
                    </div>
                </div>

                @if($link)
                    <a href="{{ $link }}" class="cart-btn cart-btn--primary promo-detail__cta">
                        {{ $cta }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endif

                <a href="{{ route('user.notifications.index') }}" class="promo-detail__back">
                    ← Về danh sách thông báo
                </a>
            </aside>
        </div>
    </div>
</section>

<script>
(() => {
    const btn = document.querySelector('[data-copy]');
    if (!btn) return;
    const label = btn.querySelector('[data-copy-label]');
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(btn.dataset.copy);
            const old = label.textContent;
            label.textContent = 'Đã sao chép!';
            btn.classList.add('is-copied');
            setTimeout(() => {
                label.textContent = old;
                btn.classList.remove('is-copied');
            }, 1800);
        } catch (e) {
            label.textContent = 'Lỗi';
        }
    });
})();
</script>
@endsection
