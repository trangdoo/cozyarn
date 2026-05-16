<?php

namespace App\Http\Controllers\Admin;

use App\Support\AdminInbox;
use App\Support\ChatThreads;
use App\Support\Notifications;
use App\Models\ChatThread;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * Admin chat — DB-backed.
 * URL identifier dùng PK numeric (vì nhiều user có cùng thread_key như 'shop').
 *
 * Tính năng:
 *  1. Sidebar danh sách hội thoại (pinned trước, sort updated_at)
 *  2. Xem nội dung + tin nhắn
 *  3. Gửi reply (DB persist)
 *  4. Đính kèm ảnh
 *  5. Tìm kiếm (client-side trong sidebar)
 *  6. Ghim / bỏ ghim
 *  7. Xoá hội thoại
 *  8. Mute / unmute
 *  9. Read receipts (last_read_by_shop)
 */
class ChatController extends Controller
{
    public function index()
    {
        AdminInbox::markTypeRead('message');

        return view('admin.chat.index', [
            'threads'        => ChatThreads::listAllForAdmin(),
            'activeThreadId' => null,
        ]);
    }

    public function show(string $threadId)
    {
        $pk     = $this->pk($threadId);
        $thread = ChatThreads::getById($pk);
        abort_unless($thread, 404);

        AdminInbox::markRead('CHAT-' . $pk);
        ChatThreads::markReadByShopByPk($pk);
        $thread = ChatThreads::getById($pk);

        return view('admin.chat.show', [
            'thread'         => $thread,
            'threadId'       => (string) $pk,
            'threads'        => ChatThreads::listAllForAdmin(),
            'activeThreadId' => (string) $pk,
        ]);
    }

    public function reply(Request $request, string $threadId)
    {
        $data = $request->validate([
            'content' => 'nullable|string|max:2000',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $hasText  = !empty(trim((string) ($data['content'] ?? '')));
        $hasImage = $request->hasFile('image');
        if (!$hasText && !$hasImage) {
            return back()->withErrors(['content' => 'Nhập tin nhắn hoặc chọn ảnh để gửi.']);
        }

        $pk = $this->pk($threadId);

        $imagePath = $hasImage ? $this->uploadImage($request->file('image')) : null;

        $result = ChatThreads::appendMessageByShop(
            $pk,
            $hasText ? $data['content'] : null,
            $imagePath,
        );

        // Push notification cho user về shop reply
        $userId = $result['thread']['user_id'];
        Notifications::push([
            'id'      => 'CHAT-REPLY-' . $pk . '-' . now()->timestamp,
            'user_id' => $userId,
            'type'    => 'system',
            'title'   => 'Shop vừa trả lời tin nhắn',
            'content' => $hasText ? mb_substr($data['content'], 0, 140) : '📷 Shop đã gửi một ảnh',
            'link'    => route('user.chat.thread', ['threadId' => $result['thread']['thread_key']]),
            'icon'    => 'info',
            'meta'    => ['thread_pk' => $pk],
        ]);

        return redirect()->route('admin.chat.show', ['threadId' => $pk]);
    }

    public function togglePin(string $threadId)
    {
        $pinned = ChatThreads::togglePinByPk($this->pk($threadId));
        return back()->with('cart_flash', $pinned ? 'Đã ghim hội thoại.' : 'Đã bỏ ghim.');
    }

    public function toggleMute(string $threadId)
    {
        $muted = ChatThreads::toggleMuteByPk($this->pk($threadId));
        return back()->with('cart_flash', $muted ? 'Đã tắt thông báo.' : 'Đã bật thông báo.');
    }

    public function destroy(string $threadId)
    {
        ChatThreads::destroyByPk($this->pk($threadId));
        return redirect()->route('admin.chat.index')->with('cart_flash', 'Đã xoá hội thoại.');
    }

    /* ═══════════════════════════ helpers ═══════════════════════════ */

    private function pk(string $threadId): int
    {
        abort_unless(is_numeric($threadId), 404);
        return (int) $threadId;
    }

    private function uploadImage($file): string
    {
        $ext  = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $name = Str::uuid()->toString() . '.' . $ext;
        $dest = public_path('uploads/chat');
        if (!is_dir($dest)) @mkdir($dest, 0755, true);
        $file->move($dest, $name);
        return '/uploads/chat/' . $name;
    }
}
