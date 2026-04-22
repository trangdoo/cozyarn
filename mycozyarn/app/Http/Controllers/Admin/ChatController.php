<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * Admin chat — đọc session('chats') của admin.
 * Reply đẩy tin nhắn sender='shop' vào thread.
 */
class ChatController extends Controller
{
    public function index()
    {
        $threads = array_values(session('chats', []));
        usort($threads, fn($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));

        return view('admin.chat.index', [
            'threads' => $threads,
            'activeThreadId' => null,
        ]);
    }

    public function show(string $threadId)
    {
        $all = session('chats', []);
        $thread = $all[$threadId] ?? null;
        abort_unless($thread, 404);

        $threads = array_values($all);
        usort($threads, fn($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));

        return view('admin.chat.show', [
            'thread'   => $thread,
            'threadId' => $threadId,
            'threads'  => $threads,
            'activeThreadId' => $threadId,
        ]);
    }

    public function reply(Request $request, string $threadId)
    {
        $data = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $all = session('chats', []);
        abort_unless(isset($all[$threadId]), 404);

        $all[$threadId]['messages'][] = [
            'id'         => (string) Str::uuid(),
            'sender'     => 'shop',
            'content'    => $data['content'],
            'image'      => null,
            'created_at' => now()->toDateTimeString(),
        ];
        $all[$threadId]['updated_at']   = now()->toDateTimeString();
        $all[$threadId]['last_preview'] = mb_substr($data['content'], 0, 80);

        session(['chats' => $all]);
        return redirect()->route('admin.chat.show', ['threadId' => $threadId]);
    }
}
