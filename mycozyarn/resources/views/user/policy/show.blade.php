@extends('layouts.public')

@section('title', $policy['title'] . ' · CozyYarn')

@section('content')
<section class="policy-hero">
    <div class="policy-hero__deco policy-hero__deco--1" aria-hidden="true"></div>
    <div class="policy-hero__deco policy-hero__deco--2" aria-hidden="true"></div>

    <div class="policy-hero__inner">
        <nav class="policy-breadcrumb" aria-label="breadcrumb">
            <a href="/">Trang chủ</a>
            <span aria-hidden="true">›</span>
            <span>Chính sách</span>
            <span aria-hidden="true">›</span>
            <span class="policy-breadcrumb__current">{{ $policy['title'] }}</span>
        </nav>
        <span class="section-chip">{{ $policy['chip'] ?? 'Chính sách' }}</span>
        <h1 class="policy-hero__title">{{ $policy['title'] }}</h1>
        <p class="policy-hero__sub">{{ $policy['intro'] }}</p>
        <p class="policy-hero__updated">Cập nhật lần cuối: {{ $policy['updated'] ?? '01/01/2025' }}</p>
    </div>
</section>

<section class="policy-body">
    <div class="policy-body__inner">

        {{-- Side nav: list all policies --}}
        <aside class="policy-aside" data-reveal>
            <h3 class="policy-aside__title">Mục khác</h3>
            <ul class="policy-aside__list">
                @foreach(($allPolicies ?? []) as $slug => $p)
                    <li>
                        <a href="/chinh-sach/{{ $slug }}"
                           class="@if($slug === ($policy['slug'] ?? '')) is-active @endif">
                            {{ $p['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="policy-aside__cta">
                <p>Cần thêm hỗ trợ?</p>
                <a href="/#contact" class="policy-aside__btn">Liên hệ ngay</a>
            </div>
        </aside>

        {{-- Main content --}}
        <article class="policy-article" data-reveal>
            @foreach($policy['sections'] as $idx => $section)
                <div class="policy-section">
                    <h2 class="policy-section__title">
                        <span class="policy-section__num">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        {{ $section['title'] }}
                    </h2>
                    <div class="policy-section__body">
                        {!! $section['body'] !!}
                    </div>
                </div>
            @endforeach

            <div class="policy-note">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M12 8v5M12 16.2V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <p>Mọi thắc mắc vui lòng liên hệ qua hotline <strong>0123.456.789</strong> hoặc email <strong>hello@cozyyarn.vn</strong>. CozyYarn luôn sẵn sàng hỗ trợ bạn trong giờ làm việc.</p>
            </div>
        </article>

    </div>
</section>
@endsection
