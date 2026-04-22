<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị — CozyYarn')</title>
    @vite(['resources/css/home.css', 'resources/js/public.js'])
    @stack('head')
</head>
<body class="admin-body">
    <div class="admin-shell">
        @include('admin.partials.sidebar')
        <div class="admin-overlay" data-admin-menu-overlay></div>

        <div class="admin-main">
            @include('admin.partials.topbar')

            <main class="admin-content">
                @if(session('cart_flash'))
                    <div class="admin-toast" data-toast>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/>
                        </svg>
                        <span>{{ session('cart_flash') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
    (() => {
        const toast = document.querySelector('[data-toast]');
        if (toast) {
            setTimeout(() => toast.classList.add('is-leaving'), 2500);
            setTimeout(() => toast.remove(), 3200);
        }
        const toggle = document.querySelector('[data-admin-menu-toggle]');
        const shell  = document.querySelector('.admin-shell');
        toggle?.addEventListener('click', () => shell.classList.toggle('is-menu-open'));
        document.querySelector('[data-admin-menu-overlay]')?.addEventListener('click', () => shell.classList.remove('is-menu-open'));
    })();
    </script>
</body>
</html>
