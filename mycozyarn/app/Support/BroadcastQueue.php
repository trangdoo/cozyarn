<?php

namespace App\Support;

/**
 * BroadcastQueue — lưu các notification admin gửi broadcast vào JSON file
 * để mọi user đều có thể đọc (vượt qua giới hạn session-per-user trong demo).
 *
 * Mỗi record:
 *  id, type (promo), title, content, icon, link, meta,
 *  recipients: 'all' | 'role:user' | 'role:admin' | ['email@...', 'email2...'],
 *  send_at, created_at, sender_id, delivered_to (tracking array to prevent double-push)
 *
 * Khi migrate DB: đổi thành bảng `broadcasts` + `broadcast_deliveries`.
 */
class BroadcastQueue
{
    public static function all(): array
    {
        $path = self::path();
        if (!file_exists($path)) return [];
        $data = json_decode((string) file_get_contents($path), true);
        return \is_array($data) ? $data : [];
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $b) {
            if (($b['id'] ?? null) === $id) return $b;
        }
        return null;
    }

    public static function save(array $broadcast): void
    {
        $all = self::all();
        $found = false;
        foreach ($all as $i => $b) {
            if (($b['id'] ?? null) === $broadcast['id']) {
                $all[$i] = $broadcast;
                $found = true;
                break;
            }
        }
        if (!$found) $all[] = $broadcast;
        self::write($all);
    }

    public static function delete(string $id): void
    {
        $all = array_values(array_filter(self::all(), fn($b) => ($b['id'] ?? null) !== $id));
        self::write($all);
    }

    public static function deleteMany(array $ids): int
    {
        $all = self::all();
        $before = \count($all);
        $all = array_values(array_filter($all, fn($b) => !\in_array($b['id'] ?? '', $ids, true)));
        self::write($all);
        return $before - \count($all);
    }

    public static function markDelivered(string $id, int $userId): void
    {
        $all = self::all();
        foreach ($all as $i => $b) {
            if (($b['id'] ?? null) === $id) {
                $to = $b['delivered_to'] ?? [];
                if (!\in_array($userId, $to, true)) $to[] = $userId;
                $all[$i]['delivered_to'] = $to;
                self::write($all);
                return;
            }
        }
    }

    /**
     * Các broadcast mà user này đủ điều kiện nhận (chưa nhận + đã đến giờ + khớp recipients).
     */
    public static function deliverableFor(int $userId, string $userEmail, string $userRole): array
    {
        $now = now()->toDateTimeString();
        $matches = [];
        foreach (self::all() as $b) {
            if (($b['send_at'] ?? $now) > $now) continue; // chưa đến giờ
            if (\in_array($userId, $b['delivered_to'] ?? [], true)) continue; // đã gửi rồi

            if (!self::matchesRecipient($b['recipients'] ?? 'all', $userId, $userEmail, $userRole)) continue;
            $matches[] = $b;
        }
        return $matches;
    }

    private static function matchesRecipient(mixed $recipients, int $userId, string $userEmail, string $userRole): bool
    {
        if ($recipients === 'all') return true;
        if ($recipients === 'role:user')  return $userRole === 'user';
        if ($recipients === 'role:admin') return $userRole === 'admin';
        if (\is_array($recipients)) {
            // Mảng email hoặc user_id
            foreach ($recipients as $r) {
                if ($r === $userEmail) return true;
                if ((int) $r === $userId) return true;
            }
        }
        return false;
    }

    private static function path(): string
    {
        $dir = storage_path('app/demo');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir . DIRECTORY_SEPARATOR . 'broadcasts.json';
    }

    private static function write(array $data): void
    {
        file_put_contents(
            self::path(),
            json_encode(array_values($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}
