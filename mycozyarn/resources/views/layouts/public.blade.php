<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Yêu cầu trình duyệt tự nâng cấp mọi request HTTP → HTTPS khi site có SSL.
         Tránh cảnh báo "form không bảo mật" của Chrome khi submit POST. --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>@yield('title', 'CozyYarn')</title>
    @vite(['resources/css/home.css', 'resources/js/public.js'])
    {{-- Skin <link> + plugin hook (home.top / site.body_start) được inject tự động
         bởi App\Http\Middleware\ApplyTheme — không cần khai báo lại ở đây để
         tránh duplicate cho các view standalone (user/home/index, user/about, ...). --}}
    @stack('head')
</head>
<body>
    <main class="home-page">
        @include('partials.site-header', ['isHome' => false])

        @yield('content')

        @include('partials.site-footer')
    </main>

    @include('partials.back-to-top')
</body>
</html>
