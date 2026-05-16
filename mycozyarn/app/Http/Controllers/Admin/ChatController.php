<?php

namespace App\Http\Controllers\Admin;

use App\Support\AdminInbox;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * Admin chat — 9 tính năng:
 * 1. Sidebar danh sách hội thoại
 * 2. Xem nội dung (quasi-realtime: user reload or auto-poll trong view)
 * 3. Gửi / nhận tin nhắn
 * 4. Đính kèm ảnh
 * 5. Tìm kiếm (client-side trong sidebar)
 * 6. Ghim hội thoại
 * 7. Xoá hội thoại
 * 8. Tắt thông báo (mute flag)
 * 9. Trạng thái đã xem (read receipts)
 */
class ChatController extends Controller
{
    public function index()
    {
        // Admin đã mở inbox chat → clear notification "tin nhắn mới" cho mọi thread.
        AdminInbox::markTypeRead('message');

        $threads = $this->sortedThreads();
        return view('admin.chat.index', [
            'threads'        => $threads,
            'activeThreadId' => null,
        ]);
    }

    public function show(string $threadId)
    {
        $all = session('chats', []);
        $thread = $all[$threadId] ?? null;
        abort_unless($thread, 404);

        // Admin đã mở thread cụ thể → mark notification của thread đó là đã đọc.
        AdminInbox::markRead('CHAT-' . $threadId);

        // Đánh dấu đã xem: admin vừa mở thread → mark all user messages as read by shop
        $all[$threadId]['last_read_by_shop'] = now()->toDateTimeString();
        session(['chats' => $all]);
        $thread = $all[$threadId];

        return view('admin.chat.show', [
            'thread'         => $thread,
            'threadId'       => $threadId,
            'threads'        => $this->sortedThreads(),
            'activeThreadId' => $threadId,
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

        $all = session('chats', []);
        abort_unless(isset($all[$threadId]), 404);

        // Upload ảnh (nếu có)
        $imagePath = null;
        if ($hasImage) {
            $file = $request->file('image');
            $ext  = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $name = Str::uuid()->toString() . '.' . $ext;
            $dest = public_path('uploads/chat');
            if (!is_dir($dest)) @mkdir($dest, 0755, true);
            $file->move($dest, $name);
            $imagePath = '/uploads/chat/' . $name;
        }

        $now = now()->toDateTimeString();
        $all[$threadId]['messages'][] = [
            'id'         => (string) Str::uuid(),
            'sender'     => 'shop',
            'content'    => $hasText ? $data['content'] : '',
            'image'      => $imagePath,
            'created_at' => $now,
        ];
        $all[$threadId]['updated_at']        = $now;
        $all[$threadId]['last_read_by_shop'] = $now;
        $all[$threadId]['last_preview']      = $hasText
            ? mb_substr($data['content'], 0, 80)
            : '📷 Shop đã gửi ảnh';

        session(['chats' => $all]);
        return redirect()->route('admin.chat.show', ['threadId' => $threadId]);
    }

    public function togglePin(string $threadId)
    {
        $all = session('chats', []);
        abort_unless(isset($all[$threadId]), 404);

        $all[$threadId]['pinned'] = !($all[$threadId]['pinned'] ?? false);
        session(['chats' => $all]);

        $msg = $all[$threadId]['pinned'] ? 'Đã ghim hội thoại.' : 'Đã bỏ ghim.';
        return back()->with('cart_flash', $msg);
    }

    public function toggleMute(string $threadId)
    {
        $all = session('chats', []);
        abort_unless(isset($all[$threadId]), 404);

        $all[$threadId]['muted'] = !($all[$threadId]['muted'] ?? false);
        session(['chats' => $all]);

        $msg = $all[$threadId]['muted'] ? 'Đã tắt thông báo.' : 'Đã bật thông báo.';
        return back()->with('cart_flash', $msg);
    }

    public function destroy(string $threadId)
    {
        $all = session('chats', []);
        unset($all[$threadId]);
        session(['chats' => $all]);

        return redirect()->route('admin.chat.index')->with('cart_flash', 'Đã xoá hội thoại.');
    }

    /**
     * Sort: pinned trước → updated_at mới nhất.
     * Thêm field computed: unread (số tin user gửi sau last_read_by_shop).
     */
    private function sortedThreads(): array
    {
        $threads = array_values(session('chats', []));

        foreach ($threads as &$t) {
            $lastRead = $t['last_read_by_shop'] ?? '1970-01-01 00:00:00';
            $t['unread_count'] = \count(array_filter($t['messages'] ?? [], fn($m) =>
                ($m['sender'] ?? '') === 'user' && ($m['created_at'] ?? '') > $lastRead
            ));
        }
        unset($t);

        usort($threads, function ($a, $b) {
            $pinA = $a['pinned'] ?? false;
            $pinB = $b['pinned'] ?? false;
            if ($pinA !== $pinB) return $pinB <=> $pinA;
            return strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? '');
        });

        return $threads;
    }
}
