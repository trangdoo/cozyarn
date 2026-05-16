<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatThread extends Model
{
    protected $fillable = [
        'thread_key',
        'user_id',
        'title',
        'subtitle',
        'type',
        'product_meta',
        'pinned',
        'muted',
        'last_read_by_user',
        'last_read_by_shop',
        'last_preview',
    ];

    protected $casts = [
        'product_meta'      => 'array',
        'pinned'            => 'boolean',
        'muted'             => 'boolean',
        'last_read_by_user' => 'datetime',
        'last_read_by_shop' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'thread_id')->orderBy('created_at');
    }

    /**
     * Số tin nhắn từ user mà shop chưa đọc (cho admin).
     */
    public function unreadForShop(): int
    {
        $last = $this->last_read_by_shop;
        $q = $this->messages()->where('sender_type', 'user');
        if ($last) $q->where('created_at', '>', $last);
        return $q->count();
    }
}
