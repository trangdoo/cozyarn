<?php

namespace App\Plugin;

/**
 * Quản lý vòng đời plugin:
 *   - Discover: quét app/Plugins/{Name}/Plugin.php
 *   - Persist enabled state vào storage/app/plugins.json
 *   - Boot tất cả plugin đang bật (gọi vào AppServiceProvider::boot)
 *
 * Đây là "Plugin loader" — hạt nhân của phần "Lập trình tùy biến chức năng" trong BCCĐ.
 */
class PluginManager
{
    private const STATE_FILE = 'plugins.json';
    private const PLUGIN_DIR = 'app/Plugins';
    private const PLUGIN_NS  = 'App\\Plugins\\';

    /** @var array<string, Plugin>|null Cache instance theo key sau lần đầu discover. */
    private static ?array $registry = null;

    /** Discover tất cả plugin trong app/Plugins/{Name}/Plugin.php — instance hoá để đọc metadata. */
    public static function all(): array
    {
        if (self::$registry !== null) {
            return self::$registry;
        }
        self::$registry = [];

        $root = base_path(self::PLUGIN_DIR);
        if (!is_dir($root)) {
            return self::$registry;
        }

        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $pluginFile = $root . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'Plugin.php';
            if (!is_file($pluginFile)) continue;

            $class = self::PLUGIN_NS . $entry . '\\Plugin';
            if (!class_exists($class)) {
                try {
                    require_once $pluginFile;
                } catch (\Throwable $e) {
                    @\Illuminate\Support\Facades\Log::warning(
                        "Plugin file failed to load: {$pluginFile}",
                        ['error' => $e->getMessage()],
                    );
                    continue;
                }
            }
            if (!class_exists($class)) {
                @\Illuminate\Support\Facades\Log::warning(
                    "Plugin class not found after include: expected {$class} in {$pluginFile}",
                );
                continue;
            }
            if (!is_subclass_of($class, Plugin::class)) {
                @\Illuminate\Support\Facades\Log::warning(
                    "Plugin {$class} does not extend App\\Plugin\\Plugin — bỏ qua.",
                );
                continue;
            }
            try {
                $instance = new $class();
                self::$registry[$instance->key()] = $instance;
            } catch (\Throwable $e) {
                @\Illuminate\Support\Facades\Log::warning(
                    "Plugin {$class} constructor threw — bỏ qua.",
                    ['error' => $e->getMessage()],
                );
            }
        }
        return self::$registry;
    }

    public static function get(string $key): ?Plugin
    {
        return self::all()[$key] ?? null;
    }

    /** Mảng key của các plugin đang được bật (persist trong storage/app/plugins.json). */
    public static function enabledKeys(): array
    {
        $path = storage_path('app/' . self::STATE_FILE);
        if (!is_file($path)) return [];
        $data = json_decode((string) @file_get_contents($path), true);
        return is_array($data['enabled'] ?? null) ? $data['enabled'] : [];
    }

    /** Plugin đang active (intersect enabled state với plugin còn tồn tại). */
    public static function active(): array
    {
        $registry = self::all();
        $enabled  = self::enabledKeys();
        $out = [];
        foreach ($enabled as $k) {
            if (isset($registry[$k])) $out[$k] = $registry[$k];
        }
        return $out;
    }

    public static function enable(string $key): bool
    {
        if (!isset(self::all()[$key])) return false;
        $keys = self::enabledKeys();
        if (!\in_array($key, $keys, true)) {
            $keys[] = $key;
        }
        return self::persist($keys);
    }

    public static function disable(string $key): bool
    {
        $keys = array_values(array_filter(self::enabledKeys(), fn($k) => $k !== $key));
        return self::persist($keys);
    }

    /** Gọi boot() trên mọi plugin đang bật — được AppServiceProvider gọi 1 lần mỗi request. */
    public static function bootActive(): void
    {
        foreach (self::active() as $plugin) {
            try {
                $plugin->boot();
            } catch (\Throwable $e) {
                // Không để plugin lỗi sập cả app — log rồi đi tiếp.
                @\Illuminate\Support\Facades\Log::warning(
                    'Plugin boot failed: ' . $plugin->key(),
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    private static function persist(array $keys): bool
    {
        $path = storage_path('app/' . self::STATE_FILE);
        $dir  = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $json = json_encode(
            ['enabled' => array_values(array_unique($keys))],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        return @file_put_contents($path, $json) !== false;
    }
}
