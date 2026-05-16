<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Admin inbox — kho thông báo dùng chung cho tất cả admin (đơn mới, tin nhắn mới,
 * thanh toán xác nhận...). Vì các phần khác của hệ thống đang là session-based per-user
 * (không share giữa sessions), ta cần một kho riêng accessible cross-session.
 *
 * Dùng Cache file driver → lưu ở storage/framework/cache/, đọc được từ mọi process.
 *
 * Mỗi item:
 *   - id          string
 *   - type        order_new | order_paid | message | system
 *   - title       string
 *   - content     string
 *   - link        ?string  (admin URL)
 *   - is_read     bool
 *   - created_at  Y-m-d H:i:s
 *   - meta        array
 */
class AdminInbox
{
    private const KEY  = 'admin_inbox_v1';
    private const MAX  = 500;
    private const STORE = 'file';

    /** Push 1 thông báo mới. Trả về id. */
    public static function push(array $attrs): string
    {
        $list = self::all();
        $id   = $attrs['id'] ?? ('AN' . strtoupper(Str::random(10)));

        $list[$id] = [
            'id'         => $id,
            'type'       => $attrs['type']    ?? 'system',
            'title'      => (string) ($attrs['title']   ?? ''),
            'content'    => (string) ($attrs['content'] ?? ''),
            'link'       => $attrs['link']    ?? null,
            'is_read'    => false,
            'created_at' => $attrs['created_at'] ?? now()->toDateTimeString(),
            'meta'       => $attrs['meta']    ?? [],
        ];

        self::trimAndSave($list);
        return $id;
    }

    public static function all(): array
    {
        return Cache::store(self::STORE)->get(self::KEY, []);
    }

    /** Lấy mới nhất trước, tối đa $limit item. */
    public static function recent(int $limit = 20): array
    {
        $all = self::all();
        uasort($all, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return array_slice($all, 0, $limit, true);
    }

    /** Đếm unread. Nếu truyền $type thì lọc theo type. */
    public static function unreadCount(?string $type = null): int
    {
        $all = self::all();
        $c   = 0;
        foreach ($all as $n) {
            if (!empty($n['is_read'])) continue;
            if ($type !== null && ($n['type'] ?? '') !== $type) continue;
            $c++;
        }
        return $c;
    }

    public static function markRead(string $id): void
    {
        $list = self::all();
        if (!isset($list[$id])) return;
        $list[$id]['is_read'] = true;
        self::save($list);
    }

    /**
     * Đánh dấu read theo type — admin mở trang Đơn hàng → mark all `order_new` +
     * `order_paid` đã đọc; admin mở Tin nhắn → mark `message` đã đọc.
     */
    public static function markTypeRead(string|array $types): void
    {
        $types = (array) $types;
        $list  = self::all();
        foreach ($list as $id => $n) {
            if (in_array($n['type'] ?? '', $types, true)) {
                $list[$id]['is_read'] = true;
            }
        }
        self::save($list);
    }

    public static function markAllRead(): void
    {
        $list = self::all();
        foreach ($list as $id => $n) {
            $list[$id]['is_read'] = true;
        }
        self::save($list);
    }

    private static function trimAndSave(array $list): void
    {
        if (count($list) > self::MAX) {
            uasort($list, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
            $list = array_slice($list, 0, self::MAX, true);
        }
        self::save($list);
    }

    private static function save(array $list): void
    {
        Cache::store(self::STORE)->forever(self::KEY, $list);
    }
}
