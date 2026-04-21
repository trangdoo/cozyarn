<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CozyYarn')</title>
    @vite(['resources/css/home.css', 'resources/js/public.js'])
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
