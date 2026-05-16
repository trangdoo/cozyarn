<?php

namespace App\Support;

use App\Models\AdminNotification;
use Illuminate\Support\Str;

/**
 * Admin inbox — DB-backed. Dùng chung cho mọi admin (cross-session).
 *
 * Public API giữ nguyên: push/all/recent/unreadCount/markRead/markTypeRead/markAllRead.
 * Item shape (back-compat với code/view cũ):
 *   id (string — notif_key hoặc PK), type, title, content, link, is_read, created_at, meta
 *
 * notif_key deterministic: 'CHAT-{threadId}', 'ORDER-NEW-{id}', 'ORDER-PAID-{id}'.
 * Item không có notif_key vẫn được, id = "AN{random}".
 */
class AdminInbox
{
    private const MAX = 500;

    public static function push(array $attrs): string
    {
        $key = $attrs['id'] ?? $attrs['notif_key'] ?? ('AN' . strtoupper(Str::random(10)));

        // Nếu đã tồn tại (deterministic key) → reset thành unread + cập nhật content.
        $existing = AdminNotification::where('notif_key', $key)->first();
        if ($existing) {
            $existing->fill([
                'type'    => $attrs['type']    ?? $existing->type,
                'title'   => $attrs['title']   ?? $existing->title,
                'content' => $attrs['content'] ?? $existing->content,
                'link'    => $attrs['link']    ?? $existing->link,
                'meta'    => $attrs['meta']    ?? $existing->meta,
                'is_read' => false,
                'read_at' => null,
            ])->save();
            self::trim();
            return $key;
        }

        AdminNotification::create([
            'notif_key'  => $key,
            'type'       => $attrs['type']    ?? 'system',
            'title'      => (string) ($attrs['title']   ?? ''),
            'content'    => (string) ($attrs['content'] ?? ''),
            'link'       => $attrs['link']    ?? null,
            'is_read'    => false,
            'created_at' => $attrs['created_at'] ?? now(),
            'meta'       => $attrs['meta']    ?? [],
        ]);

        self::trim();
        return $key;
    }

    /** Trả về array keyed by notif_key/id (back-compat). */
    public static function all(): array
    {
        $rows = AdminNotification::orderByDesc('created_at')->get();
        $out  = [];
        foreach ($rows as $row) {
            $key = $row->notif_key ?: (string) $row->id;
            $out[$key] = self::toArray($row);
        }
        return $out;
    }

    public static function recent(int $limit = 20): array
    {
        $rows = AdminNotification::orderByDesc('created_at')->limit($limit)->get();
        $out  = [];
        foreach ($rows as $row) {
            $key = $row->notif_key ?: (string) $row->id;
            $out[$key] = self::toArray($row);
        }
        return $out;
    }

    public static function unreadCount(?string $type = null): int
    {
        $q = AdminNotification::where('is_read', false);
        if ($type !== null) $q->where('type', $type);
        return $q->count();
    }

    public static function markRead(string $id): void
    {
        $q = AdminNotification::query();
        $q = is_numeric($id) ? $q->where('id', (int) $id) : $q->where('notif_key', $id);
        $q->update(['is_read' => true, 'read_at' => now()]);
    }

    public static function markTypeRead(string|array $types): void
    {
        $types = (array) $types;
        AdminNotification::whereIn('type', $types)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public static function markAllRead(): void
    {
        AdminNotification::where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /* ═══════════════════════════ private helpers ═══════════════════════════ */

    private static function toArray($row): array
    {
        return [
            'id'         => $row->notif_key ?: (string) $row->id,
            'pk'         => $row->id,
            'notif_key'  => $row->notif_key,
            'type'       => $row->type,
            'title'      => $row->title,
            'content'    => $row->content ?? '',
            'link'       => $row->link,
            'is_read'    => (bool) $row->is_read,
            'created_at' => $row->created_at?->toDateTimeString(),
            'meta'       => $row->meta ?? [],
        ];
    }

    /** Giữ tối đa MAX bản ghi — xoá cũ nhất khi vượt. */
    private static function trim(): void
    {
        $count = AdminNotification::count();
        if ($count <= self::MAX) return;

        $deleteCount = $count - self::MAX;
        $ids = AdminNotification::orderBy('created_at')->limit($deleteCount)->pluck('id');
        AdminNotification::whereIn('id', $ids)->delete();
    }
}
