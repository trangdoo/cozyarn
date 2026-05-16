<?php

namespace App\Http\Controllers;

use App\Support\AdminInbox;
use App\Support\ChatThreads;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Chat user — DB-backed via ChatThreads service.
 * Mỗi user có:
 *   - thread 'shop' (lazy-created khi mở lần đầu)
 *   - thread 'product-{categorySlug}-{productSlug}' (tạo từ trang product)
 *
 * User URLs dùng thread_key (scope theo Auth::id(), nên 1 user chỉ có 1 'shop').
 * Khi user gửi → push admin_notifications (key 'CHAT-{pk}'). Admin reply qua admin chat.
 */
class ChatController extends Controller
{
    public function inbox()
    {
        $this->ensureShopThread();
        return view('user.chat.inbox', [
            'threads'        => ChatThreads::listForUser(Auth::id()),
            'activeThreadId' => null,
        ]);
    }

    public function thread(string $threadId)
    {
        if ($threadId === ChatThreads::SHOP_THREAD) {
            $this->ensureShopThread();
        }

        $thread = ChatThreads::getForUser($threadId, Auth::id());
        abort_unless($thread, 404);

        ChatThreads::markReadByUser($threadId, Auth::id());
        $thread = ChatThreads::getForUser($threadId, Auth::id());

        return view('user.chat.thread', [
            'thread'         => $thread,
            'threadId'       => $threadId,
            'threads'        => ChatThreads::listForUser(Auth::id()),
            'activeThreadId' => $threadId,
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'thread_id' => 'required|string|max:160',
            'content'   => 'nullable|string|max:2000',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'product'   => 'nullable|array',
            'product.slug'     => 'nullable|string|max:120',
            'product.category' => 'nullable|string|max:120',
            'product.name'     => 'nullable|string|max:200',
            'product.image'    => 'nullable|string|max:300',
            'product.price'    => 'nullable|numeric',
        ]);

        $hasContent = !empty(trim((string) ($data['content'] ?? '')));
        $hasImage   = $request->hasFile('image');
        if (!$hasContent && !$hasImage) {
            return back()->withErrors(['content' => 'Vui lòng nhập tin nhắn hoặc chọn ảnh để gửi.']);
        }

        $threadKey = $data['thread_id'];
        $userId    = Auth::id();
        $thread    = ChatThreads::getForUser($threadKey, $userId, withMessages: false);

        // Tạo thread mới nếu chưa có
        if (!$thread) {
            if ($threadKey === ChatThreads::SHOP_THREAD) {
                $thread = $this->ensureShopThread();
            } elseif (str_starts_with($threadKey, 'product-') && !empty($data['product'])) {
                $p = $data['product'];
                $thread = ChatThreads::findOrCreateForUser($threadKey, $userId, [
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
            } else {
                abort(404);
            }
        }

        $imagePath = $hasImage ? $this->uploadImage($request->file('image')) : null;

        ChatThreads::appendMessageByUser(
            $threadKey,
            $userId,
            $hasContent ? $data['content'] : null,
            $imagePath,
        );

        // Push admin inbox: deterministic key per thread PK → tin liên tục cùng thread chỉ tạo 1 notification.
        $userName = Auth::user()->name ?? 'Khách';
        $threadPk = $thread['pk'];
        AdminInbox::push([
            'id'      => 'CHAT-' . $threadPk,
            'type'    => 'message',
            'title'   => "Tin nhắn mới từ {$userName}",
            'content' => $hasContent
                ? mb_substr($data['content'], 0, 140)
                : '📷 Đã gửi một ảnh',
            'link'    => route('admin.chat.show', ['threadId' => $threadPk]),
            'meta'    => ['thread_pk' => $threadPk, 'thread_key' => $threadKey, 'user_id' => $userId],
        ]);

        return redirect()->route('user.chat.thread', ['threadId' => $threadKey]);
    }

    /* ═══════════════════════════ helpers ═══════════════════════════ */

    private function ensureShopThread(): array
    {
        return ChatThreads::findOrCreateForUser(ChatThreads::SHOP_THREAD, Auth::id(), [
            'title'    => 'CozyYarn Shop',
            'subtitle' => 'Gửi tin nhắn cho shop — bất cứ câu hỏi nào.',
            'type'     => 'shop',
        ]);
    }

    private function uploadImage($file): string
    {
        $ext  = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $name = Str::uuid()->toString() . '.' . $ext;
        $dest = public_path('uploads/chat');
        if (!is_dir($dest)) {
            @mkdir($dest, 0755, true);
        }
        $file->move($dest, $name);
        return '/uploads/chat/' . $name;
    }
}
