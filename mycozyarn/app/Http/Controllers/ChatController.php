<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Chat với shop — session-based, không real-time.
 * Mỗi user có 1 số "thread" (cuộc hội thoại):
 *  - thread 'shop'                       → chat chung với shop
 *  - thread 'product-{categorySlug}-{productSlug}' → chat về 1 sản phẩm cụ thể
 *
 * Dữ liệu lưu ở session('chats'): [ thread_id => [ ...meta, 'messages' => [...] ] ],
 * có key 'user_id' để mỗi user chỉ thấy thread của mình.
 */
class ChatController extends Controller
{
    private const SHOP_THREAD = 'shop';
    private const AUTO_REPLY  = 'Cảm ơn bạn đã liên hệ CozyYarn! Shop đã nhận tin nhắn và sẽ phản hồi chi tiết trong ít phút. ♡';

    public function inbox()
    {
        return view('user.chat.inbox', [
            'threads'        => $this->listThreads(),
            'activeThreadId' => null,
        ]);
    }

    public function thread(string $threadId)
    {
        $all    = session('chats', []);
        $thread = $all[$threadId] ?? null;

        // Thread 'shop' được tạo lazy khi user truy cập lần đầu
        if (!$thread && $threadId === self::SHOP_THREAD) {
            $thread = $this->makeThread(self::SHOP_THREAD, [
                'title'    => 'CozyYarn Shop',
                'subtitle' => 'Gửi tin nhắn cho shop — bất cứ câu hỏi nào.',
                'type'     => 'shop',
            ]);
            $all[self::SHOP_THREAD] = $thread;
            session(['chats' => $all]);
        }

        abort_unless($thread, 404);
        abort_unless(($thread['user_id'] ?? null) === Auth::id(), 403);

        return view('user.chat.thread', [
            'thread'         => $thread,
            'threadId'       => $threadId,
            'threads'        => $this->listThreads(),
            'activeThreadId' => $threadId,
        ]);
    }

    /**
     * Danh sách thread của user (đảm bảo có thread 'shop' + sắp xếp mới → cũ).
     */
    private function listThreads(): array
    {
        $threads = $this->userThreads();

        if (!isset($threads[self::SHOP_THREAD])) {
            $threads[self::SHOP_THREAD] = $this->makeThread(self::SHOP_THREAD, [
                'title'    => 'CozyYarn Shop',
                'subtitle' => 'Gửi tin nhắn cho shop — bất cứ câu hỏi nào.',
                'type'     => 'shop',
            ]);
        }

        uasort($threads, fn($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));
        return $threads;
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'thread_id' => 'required|string|max:120',
            'content'   => 'required|string|max:2000',
            // optional: nếu tạo thread mới từ trang product
            'product'   => 'nullable|array',
            'product.slug'       => 'nullable|string|max:120',
            'product.category'   => 'nullable|string|max:120',
            'product.name'       => 'nullable|string|max:200',
            'product.image'      => 'nullable|string|max:300',
            'product.price'      => 'nullable|numeric',
        ]);

        $threadId = $data['thread_id'];
        $all      = session('chats', []);
        $thread   = $all[$threadId] ?? null;

        // Tạo thread mới nếu chưa có (cho product-*)
        if (!$thread) {
            if (str_starts_with($threadId, 'product-') && !empty($data['product'])) {
                $p = $data['product'];
                $thread = $this->makeThread($threadId, [
                    'title'    => $p['name'] ?? 'Sản phẩm',
                    'subtitle' => 'Trao đổi về sản phẩm này',
                    'type'     => 'product',
                    'product'  => [
                        'slug'     => $p['slug']     ?? '',
                        'category' => $p['category'] ?? '',
                        'name'     => $p['name']     ?? '',
                        'image'    => $p['image']    ?? '',
                        'price'    => isset($p['price']) ? (float) $p['price'] : 0,
                    ],
                ]);
            } elseif ($threadId === self::SHOP_THREAD) {
                $thread = $this->makeThread(self::SHOP_THREAD, [
                    'title'    => 'CozyYarn Shop',
                    'subtitle' => 'Gửi tin nhắn cho shop — bất cứ câu hỏi nào.',
                    'type'     => 'shop',
                ]);
            } else {
                abort(404);
            }
        }

        abort_unless(($thread['user_id'] ?? null) === Auth::id(), 403);

        $now = now()->toDateTimeString();
        $thread['messages'][] = [
            'id'         => (string) Str::uuid(),
            'sender'     => 'user',
            'content'    => $data['content'],
            'created_at' => $now,
        ];
        // Auto-reply từ shop (demo — không có admin thật)
        $thread['messages'][] = [
            'id'         => (string) Str::uuid(),
            'sender'     => 'shop',
            'content'    => self::AUTO_REPLY,
            'created_at' => now()->addSecond()->toDateTimeString(),
        ];
        $thread['updated_at']    = $now;
        $thread['last_preview']  = mb_substr($data['content'], 0, 80);

        $all[$threadId] = $thread;
        session(['chats' => $all]);

        return redirect()->route('user.chat.thread', ['threadId' => $threadId]);
    }

    /**
     * Build 1 thread mới (chưa có tin nhắn).
     */
    private function makeThread(string $id, array $meta): array
    {
        return [
            'id'           => $id,
            'user_id'      => Auth::id(),
            'title'        => $meta['title']    ?? 'Hội thoại',
            'subtitle'     => $meta['subtitle'] ?? '',
            'type'         => $meta['type']     ?? 'shop',
            'product'      => $meta['product']  ?? null,
            'messages'     => [],
            'created_at'   => now()->toDateTimeString(),
            'updated_at'   => now()->toDateTimeString(),
            'last_preview' => '',
        ];
    }

    /**
     * Danh sách thread thuộc về user đang đăng nhập.
     */
    private function userThreads(): array
    {
        $all = session('chats', []);
        return array_filter($all, fn($t) => ($t['user_id'] ?? null) === Auth::id());
    }
}
