<?php

namespace App\Providers;

use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bind các interface tầng Repository sang implementation Eloquent.
     * Khi cần đổi (vd: fake repo cho test), chỉ sửa ở đây.
     */
    public array $bindings = [
        UserRepositoryInterface::class     => UserRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        ProductRepositoryInterface::class  => ProductRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Khởi động tất cả plugin đang được bật (từ storage/app/plugins.json).
        // Mỗi plugin tự đăng ký listener vào App\Plugin\Hook ở boot().
        \App\Plugin\PluginManager::bootActive();

        // Force HTTPS khi deploy phía sau reverse proxy / CDN (vd: Cloudflare, Nginx)
        // — link/form action sẽ được render https, tránh cảnh báo "form không bảo mật"
        // của Chrome khi submit POST. Bật bằng APP_URL=https://... trong .env.
        $appUrl = (string) config('app.url', '');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
