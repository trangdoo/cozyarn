<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Áp dụng cho mọi request web: kiểm tra trạng thái 'blocked' trên user
        // đang đăng nhập sau khi session đã được khởi động — đẩy user bị khoá
        // ra ngay lập tức, không cần đợi logout thủ công.
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserActive::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);

        // SePay gọi từ server ngoài, không có CSRF token — xác thực bằng HMAC trong controller.
        $middleware->validateCsrfTokens(except: [
            'webhook/sepay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
