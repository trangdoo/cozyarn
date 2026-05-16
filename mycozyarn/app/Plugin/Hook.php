<?php

namespace App\Plugin;

/**
 * Hệ thống hook (filter/action) đơn giản kiểu WordPress.
 *
 *   • Hook::listen('home.top', fn() => '<div>...</div>', priority: 10)
 *   • Hook::render('home.top')   — gộp tất cả output của các listener
 *   • Hook::filter('checkout.total', $total, $order)  — pipe value qua các listener
 *
 * Plugins đăng ký vào hook trong boot(); view/controller core gọi render()/filter()
 * tại các điểm cắm (extension points) đã định nghĩa sẵn.
 */
class Hook
{
    /** @var array<string, array<int, array<int, callable>>> [event => [priority => [callable, ...]]] */
    private static array $listeners = [];

    public static function listen(string $event, callable $callback, int $priority = 10): void
    {
        self::$listeners[$event][$priority][] = $callback;
    }

    /**
     * Gọi tất cả listener của 1 event, gộp string trả về của chúng thành 1 chuỗi.
     * Dùng cho hook kiểu "render content" (chèn HTML vào view).
     *
     * Mọi listener được bọc try/catch — 1 plugin lỗi sẽ KHÔNG làm sập cả trang,
     * chỉ output của plugin đó bị bỏ qua và log warning.
     */
    public static function render(string $event, mixed ...$args): string
    {
        if (empty(self::$listeners[$event])) {
            return '';
        }
        $buckets = self::$listeners[$event];
        ksort($buckets);
        $out = '';
        foreach ($buckets as $callbacks) {
            foreach ($callbacks as $cb) {
                try {
                    $r = $cb(...$args);
                } catch (\Throwable $e) {
                    @\Illuminate\Support\Facades\Log::warning(
                        "Plugin hook render failed: {$event}",
                        ['error' => $e->getMessage()],
                    );
                    continue;
                }
                if (is_string($r)) {
                    $out .= $r;
                }
            }
        }
        return $out;
    }

    /**
     * Pipe value qua các listener — mỗi listener nhận value hiện tại (+ extra args)
     * và trả về value mới. Dùng cho hook kiểu "transform value" (vd: chiết khấu).
     *
     * Listener throw → bỏ qua listener đó (giữ nguyên value), log warning, đi tiếp.
     */
    public static function filter(string $event, mixed $value, mixed ...$args): mixed
    {
        if (empty(self::$listeners[$event])) {
            return $value;
        }
        $buckets = self::$listeners[$event];
        ksort($buckets);
        foreach ($buckets as $callbacks) {
            foreach ($callbacks as $cb) {
                try {
                    $value = $cb($value, ...$args);
                } catch (\Throwable $e) {
                    @\Illuminate\Support\Facades\Log::warning(
                        "Plugin hook filter failed: {$event}",
                        ['error' => $e->getMessage()],
                    );
                }
            }
        }
        return $value;
    }

    /** Reset (cho test). */
    public static function reset(): void
    {
        self::$listeners = [];
    }
}
