<?php

namespace App\Support;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use Illuminate\Support\Facades\Auth;

/**
 * ChatThreads — service tập trung logic chat (DB-backed).
 *
 * Định danh thread:
 *   - User side: dùng thread_key ('shop' | 'product-{cat}-{slug}'), scope theo user_id.
 *     Mỗi user có duy nhất 1 thread per (user_id, thread_key).
 *   - Admin side: dùng PK (numeric) vì nhiều user có cùng thread_key.
 *
 * Array shape (cho views):
 *   thread: id, pk, thread_key, user_id, title, subtitle, type, product,
 *           pinned, muted, last_read_by_user, last_read_by_shop, last_preview,
 *           messages[], created_at, updated_at
 *     - User side: id = thread_key (back-compat).
 *     - Admin side: id = pk (numeric string).
 *   message: id, sender, content, image, created_at
 */
class ChatThreads
{
    public const SHOP_THREAD = 'shop';

    /* ═══════════════════════ USER-SIDE (scoped by user_id) ═══════════════════════ */

    public static function getForUser(string $threadKey, int $userId, bool $withMessages = true): ?array
    {
        $row = ChatThread::where('user_id', $userId)->where('thread_key', $threadKey)->first();
        return $row ? self::toArray($row, idMode: 'thread_key', withMessages: $withMessages) : null;
    }

    public static function listForUser(int $userId): array
    {
        $rows = ChatThread::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get();
        return $rows->map(fn ($t) => self::toArray($t, idMode: 'thread_key', withMessages: false))->all();
    }

    public static function findOrCreateForUser(string $threadKey, int $userId, array $meta): array
    {
        $row = ChatThread::firstOrCreate(
            ['user_id' => $userId, 'thread_key' => $threadKey],
            [
                'title'        => $meta['title']    ?? 'Hội thoại',
                'subtitle'     => $meta['subtitle'] ?? '',
                'type'         => $meta['type']     ?? 'shop',
                'product_meta' => $meta['product']  ?? null,
                'last_preview' => '',
            ]
        );
        return self::toArray($row, idMode: 'thread_key', withMessages: true);
    }

    public static function markReadByUser(string $threadKey, int $userId): void
    {
        ChatThread::where('user_id', $userId)->where('thread_key', $threadKey)
            ->update(['last_read_by_user' => now()]);
    }

    public static function appendMessageByUser(string $threadKey, int $userId, ?string $content, ?string $imagePath): array
    {
        $thread = ChatThread::where('user_id', $userId)->where('thread_key', $threadKey)->firstOrFail();
        return self::appendMessage($thread, 'user', $userId, $content, $imagePath);
    }

    /* ═══════════════════════ ADMIN-SIDE (lookup by PK) ═══════════════════════ */

    public static function getById(int $pk, bool $withMessages = true): ?array
    {
        $row = ChatThread::find($pk);
        return $row ? self::toArray($row, idMode: 'pk', withMessages: $withMessages) : null;
    }

    /** Tất cả thread cho admin sidebar — pinned trước, rồi mới → cũ. Có unread_count. */
    public static function listAllForAdmin(): array
    {
        $rows = ChatThread::orderByDesc('pinned')->orderByDesc('updated_at')->get();
        return $rows->map(function ($t) {
            $arr = self::toArray($t, idMode: 'pk', withMessages: false);
            $arr['unread_count'] = $t->unreadForShop();
            return $arr;
        })->all();
    }

    public static function appendMessageByShop(int $pk, ?string $content, ?string $imagePath): array
    {
        $thread = ChatThread::findOrFail($pk);
        $shopSenderId = $thread->user_id; // proxy — không có user "shop"; dùng owner để FK hợp lệ.
        return self::appendMessage($thread, 'shop', $shopSenderId, $content, $imagePath);
    }

    public static function markReadByShopByPk(int $pk): void
    {
        ChatThread::where('id', $pk)->update(['last_read_by_shop' => now()]);
    }

    public static function togglePinByPk(int $pk): bool
    {
        $t = ChatThread::findOrFail($pk);
        $t->pinned = !$t->pinned;
        $t->save();
        return $t->pinned;
    }

    public static function toggleMuteByPk(int $pk): bool
    {
        $t = ChatThread::findOrFail($pk);
        $t->muted = !$t->muted;
        $t->save();
        return $t->muted;
    }

    public static function destroyByPk(int $pk): void
    {
        ChatThread::where('id', $pk)->delete();
    }

    /* ═══════════════════════ shared helpers ═══════════════════════ */

    private static function appendMessage(ChatThread $thread, string $senderType, int $senderId, ?string $content, ?string $imagePath): array
    {
        $hasText = !empty(trim((string) $content));
        $msg = ChatMessage::create([
            'thread_id'   => $thread->id,
            'sender_id'   => $senderId,
            'sender_type' => $senderType,
            'content'     => $hasText ? $content : null,
            'image_url'   => $imagePath,
        ]);

        $preview = $hasText
            ? mb_substr($content, 0, 80)
            : ($senderType === 'shop' ? '📷 Shop đã gửi ảnh' : '📷 Đã gửi một ảnh');

        $thread->last_preview = $preview;
        $thread->updated_at   = $msg->created_at;
        if ($senderType === 'shop') $thread->last_read_by_shop = $msg->created_at;
        $thread->save();

        return [
            'thread'  => self::toArray($thread->fresh(), idMode: 'pk', withMessages: true),
            'message' => self::messageToArray($msg),
        ];
    }

    /**
     * Map ChatThread → array. $idMode: 'thread_key' (user side) | 'pk' (admin side).
     */
    private static function toArray(ChatThread $t, string $idMode, bool $withMessages): array
    {
        $out = [
            'id'                => $idMode === 'pk' ? (string) $t->id : $t->thread_key,
            'pk'                => $t->id,
            'thread_key'        => $t->thread_key,
            'user_id'           => $t->user_id,
            'title'             => $t->title,
            'subtitle'          => $t->subtitle ?? '',
            'type'              => $t->type,
            'product'           => $t->product_meta,
            'pinned'            => (bool) $t->pinned,
            'muted'             => (bool) $t->muted,
            'last_read_by_user' => $t->last_read_by_user?->toDateTimeString(),
            'last_read_by_shop' => $t->last_read_by_shop?->toDateTimeString(),
            'last_preview'      => $t->last_preview ?? '',
            'created_at'        => $t->created_at?->toDateTimeString(),
            'updated_at'        => $t->updated_at?->toDateTimeString(),
        ];

        if ($withMessages) {
            $out['messages'] = $t->messages()->get()
                ->map(fn ($m) => self::messageToArray($m))
                ->all();
        }
        return $out;
    }

    private static function messageToArray(ChatMessage $m): array
    {
        return [
            'id'         => (string) $m->id,
            'sender'     => $m->sender_type, // 'user' | 'shop'
            'content'    => $m->content ?? '',
            'image'      => $m->image_url,
            'created_at' => $m->created_at?->toDateTimeString(),
        ];
    }
}
