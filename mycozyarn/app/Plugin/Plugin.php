<?php

namespace App\Plugin;

/**
 * Lớp cơ sở mọi plugin phải kế thừa.
 *
 * Plugin lifecycle:
 *   1. Discovery — PluginManager quét app/Plugins/{Name}/Plugin.php
 *   2. Đọc metadata qua các getter (key, name, version, ...)
 *   3. Nếu plugin được bật trong storage/app/plugins.json → gọi boot()
 *   4. Plugin tự đăng ký vào Hook (xem App\Plugin\Hook) để chèn UI/logic
 *
 * Đây là phần "Lập trình tùy biến chức năng (plugin)" trong BCCĐ.
 */
abstract class Plugin
{
    /** Khoá định danh duy nhất (snake_case), dùng làm key bật/tắt. */
    abstract public function key(): string;

    /** Tên hiển thị trong admin. */
    abstract public function name(): string;

    /** Mô tả plugin làm gì. */
    abstract public function description(): string;

    /** Phiên bản plugin (semver). */
    public function version(): string
    {
        return '1.0.0';
    }

    /** Tên tác giả. */
    public function author(): string
    {
        return 'CozyYarn';
    }

    /** Plugin có cài đặt riêng không? Trả mảng cấu hình mặc định. */
    public function defaultSettings(): array
    {
        return [];
    }

    /**
     * Đăng ký hook handler hoặc đăng ký service. Chỉ chạy khi plugin được bật.
     * Plugin nên override method này — đây là entry point chính.
     */
    abstract public function boot(): void;
}
