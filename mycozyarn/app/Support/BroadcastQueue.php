<?php

namespace App\Support;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Models\User;

/**
 * BroadcastQueue — DB-backed.
 * Public API giữ nguyên cho Admin\NotificationController & Support\Notifications.
 *
 * Item shape (back-compat):
 *   id (int as string), type, title, content, icon, link, meta, recipients,
 *   send_at, created_at, sender_id, delivered_to (array of user_id)
 */
class BroadcastQueue
{
    public static function all(): array
    {
        return Broadcast::orderByDesc('created_at')->get()
            ->map(fn ($b) => self::toArray($b))
            ->all();
    }

    public static function find(string $id): ?array
    {
        $row = Broadcast::find((int) $id);
        return $row ? self::toArray($row) : null;
    }

    /**
     * Tạo mới hoặc cập nhật broadcast.
     * $broadcast['id'] (nếu có) = PK; thiếu thì tạo mới.
     */
    public static function save(array $broadcast): void
    {
        $id = $broadcast['id'] ?? null;

        $payload = [
            'sender_id'  => $broadcast['sender_id'] ?? null,
            'type'       => $broadcast['type']      ?? 'promo',
            'title'      => $broadcast['title']     ?? 'Thông báo',
            'content'    => $broadcast['content']   ?? null,
            'link'       => $broadcast['link']      ?? null,
            'icon'       => $broadcast['icon']      ?? null,
            'recipients' => self::recipientsToStorage($broadcast['recipients'] ?? 'all'),
            'meta'       => $broadcast['meta']      ?? null,
            'send_at'    => $broadcast['send_at']   ?? null,
        ];

        if ($id && ($row = Broadcast::find((int) $id))) {
            $row->fill($payload)->save();
        } else {
            Broadcast::create($payload);
        }
    }

    public static function delete(string $id): void
    {
        Broadcast::where('id', (int) $id)->delete();
    }

    public static function deleteMany(array $ids): int
    {
        $ints = array_map('intval', $ids);
        return Broadcast::whereIn('id', $ints)->delete();
    }

    public static function markDelivered(string $id, int $userId): void
    {
        BroadcastDelivery::firstOrCreate(
            ['broadcast_id' => (int) $id, 'user_id' => $userId],
            ['delivered_at' => now()]
        );
    }

    /**
     * Các broadcast user đủ điều kiện nhận (đã đến giờ + chưa nhận + khớp recipients).
     */
    public static function deliverableFor(int $userId, string $userEmail, string $userRole): array
    {
        $delivered = BroadcastDelivery::where('user_id', $userId)->pluck('broadcast_id')->all();

        $candidates = Broadcast::query()
            ->where(function ($q) {
                $q->whereNull('send_at')->orWhere('send_at', '<=', now());
            })
            ->when(!empty($delivered), fn ($q) => $q->whereNotIn('id', $delivered))
            ->orderBy('created_at')
            ->get();

        $out = [];
        foreach ($candidates as $b) {
            if (!$b->matchesUser($userId, $userEmail, $userRole)) continue;
            $out[] = self::toArray($b);
        }
        return $out;
    }

    /* ═══════════════════════════ private helpers ═══════════════════════════ */

    private static function toArray(Broadcast $b): array
    {
        return [
            'id'           => (string) $b->id,
            'sender_id'    => $b->sender_id,
            'type'         => $b->type,
            'title'        => $b->title,
            'content'      => $b->content ?? '',
            'link'         => $b->link,
            'icon'         => $b->icon,
            'recipients'   => $b->recipientsParsed(),
            'meta'         => $b->meta ?? [],
            'send_at'      => $b->send_at?->toDateTimeString(),
            'created_at'   => $b->created_at?->toDateTimeString(),
            'updated_at'   => $b->updated_at?->toDateTimeString(),
            'delivered_to' => BroadcastDelivery::where('broadcast_id', $b->id)->pluck('user_id')->all(),
        ];
    }

    private static function recipientsToStorage(mixed $r): string
    {
        if (is_string($r)) return $r;
        if (is_array($r))  return json_encode(array_values($r), JSON_UNESCAPED_UNICODE);
        return 'all';
    }
}
