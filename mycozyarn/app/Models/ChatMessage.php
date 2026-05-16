<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bảng DB là `messages` (legacy name). Model tên ChatMessage cho rõ scope.
 */
class ChatMessage extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'thread_id',
        'sender_id',
        'sender_type',
        'receiver_id',
        'content',
        'image_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ChatThread::class, 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
