<?php

namespace App\Support;

use Igaster\LaravelTheme\Facades\Theme;

/**
 * Wrapper mỏng quanh package igaster/laravel-theme.
 *
 * Nhiệm vụ:
 *   1. Liệt kê 3 theme đã đăng ký trong config/themes.php (kèm metadata UI: màu preview, mô tả).
 *   2. Trả về theme đang active (đọc từ storage/app/active_theme.txt — vì package
 *      không persist sang request kế tiếp).
 *   3. Đổi theme active (ghi file) — middleware sẽ đọc lại ở request kế tiếp.
 *   4. Trả URL skin.css của theme đang active (qua Theme::url của package, tận dụng
 *      fallback hierarchy: mint → cozy nếu mint không có file).
 *
 * Đây là phần "Tùy biến skin" của BCCĐ — thay hệ skin custom bằng package phổ biến.
 */
class ThemeManager
{
    private const STORAGE_FILE = 'active_theme.txt';
    public const DEFAULT_THEME = 'cozy';

    /** Metadata UI cho từng theme (đẩy vào trang admin). Theme key phải khớp config/themes.php. */
    private const META = [
        'cozy' => [
            'name'    => 'Cozy Pink',
            'desc'    => 'Tông hồng kem ấm áp — theme mặc định, dùng resources/css/home.css gốc.',
            'preview' => '#ffd7e3',
            'accent'  => '#d4779b',
        ],
        'mint' => [
            'name'    => 'Mint Garden',
            'desc'    => 'Tông xanh bạc hà tươi mát, phong cách Bắc Âu.',
            'preview' => '#c3e8d5',
            'accent'  => '#3d7a52',
        ],
        'night' => [
            'name'    => 'Night Mode',
            'desc'    => 'Chế độ tối, tone tím-hồng nhạt, dịu mắt buổi tối.',
            'preview' => '#2a1e2e',
            'accent'  => '#e8a4c1',
        ],
    ];

    /** Trả về metadata của TẤT CẢ theme được đăng ký trong config (giao với meta UI). */
    public static function all(): array
    {
        $out = [];
        foreach (array_keys(config('themes.themes', [])) as $key) {
            $out[$key] = self::META[$key] ?? [
                'name'    => ucfirst($key),
                'desc'    => '(Chưa có metadata UI — bổ sung trong App\\Support\\ThemeManager::META)',
                'preview' => '#cccccc',
                'accent'  => '#888888',
            ];
        }
        return $out;
    }

    public static function active(): string
    {
        $path = storage_path('app/' . self::STORAGE_FILE);
        if (is_file($path)) {
            $key = trim((string) @file_get_contents($path));
            if (array_key_exists($key, config('themes.themes', []))) {
                return $key;
            }
        }
        return config('themes.default', self::DEFAULT_THEME);
    }

    public static function setActive(string $key): bool
    {
        if (!array_key_exists($key, config('themes.themes', []))) {
            return false;
        }
        $path = storage_path('app/' . self::STORAGE_FILE);
        $dir  = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@file_put_contents($path, $key) === false) {
            return false;
        }
        // Áp dụng ngay cho request hiện tại (nếu admin xem lại trang sẽ thấy luôn).
        Theme::set($key);
        return true;
    }

    public static function meta(string $key): ?array
    {
        return self::META[$key] ?? null;
    }

    /**
     * URL skin.css của theme đang dùng — đi qua Theme::url của package, nên tự fallback
     * sang theme cha nếu file không tồn tại (mint thiếu → lấy của cozy).
     * Trả null nếu:
     *   - không tìm thấy file ở cả theme con lẫn cha
     *   - đang ở theme mặc định (cozy) — skin.css cố ý để rỗng nên không cần nạp,
     *     tiết kiệm 1 HTTP request mỗi request.
     */
    public static function skinUrl(): ?string
    {
        $active = self::active();
        if ($active === self::DEFAULT_THEME) {
            return null;
        }
        try {
            $url = Theme::url('skin.css');
            if (!$url) {
                return null;
            }
            // Cache-bust dựa vào mtime của file tương ứng trong public/.
            $file = public_path("themes/{$active}/skin.css");
            if (is_file($file)) {
                $url .= '?v=' . @filemtime($file);
            }
            return $url;
        } catch (\Throwable) {
            return null;
        }
    }
}
