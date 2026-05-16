<?php

namespace App\Plugins\WelcomeBanner;

use App\Plugin\Hook;
use App\Plugin\Plugin as BasePlugin;

/**
 * Plugin: Welcome Banner
 * Chèn 1 thanh thông báo chào mừng lên đầu trang chủ — minh hoạ cho điểm cắm
 * "home.top" (render hook). Bật/tắt từ admin → admin/plugin.
 */
class Plugin extends BasePlugin
{
    public function key(): string         { return 'welcome_banner'; }
    public function name(): string        { return 'Welcome Banner'; }
    public function description(): string { return 'Chèn banner chào mừng "Free ship đơn 500K" lên đầu trang chủ.'; }
    public function version(): string     { return '1.0.0'; }
    public function author(): string      { return 'CozyYarn Team'; }

    public function boot(): void
    {
        Hook::listen('home.top', function (): string {
            return <<<HTML
                <div style="background:linear-gradient(90deg,#ffd7e3 0%,#fbe9c1 100%);
                            color:#7f4e63;text-align:center;padding:10px 16px;
                            font-size:14px;font-weight:600;letter-spacing:.3px">
                    🎉 <span>Miễn phí vận chuyển cho mọi đơn từ <strong>500.000 ₫</strong></span>
                    &nbsp;·&nbsp;
                    <span>Nhập mã <code style="background:#fff;padding:2px 8px;border-radius:8px">COZY10</code> giảm thêm 10%</span>
                </div>
            HTML;
        }, priority: 10);
    }
}
