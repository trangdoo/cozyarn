<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    protected $fillable = [
        'sender_id',
        'type',
        'title',
        'content',
        'link',
        'icon',
        'recipients',
        'meta',
        'send_at',
    ];

    protected $casts = [
        'meta'    => 'array',
        'send_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(BroadcastDelivery::class);
    }

    /** Đã đến giờ gửi chưa? null = gửi ngay. */
    public function isReadyToSend(): bool
    {
        return $this->send_at === null || $this->send_at <= now();
    }

    /** Recipients có 3 dạng: 'all', 'role:user'|'role:admin', JSON array. */
    public function recipientsParsed(): mixed
    {
        $r = $this->recipients;
        if (in_array($r, ['all', 'role:user', 'role:admin'], true)) return $r;
        $decoded = json_decode((string) $r, true);
        return is_array($decoded) ? $decoded : 'all';
    }

    public function matchesUser(int $userId, string $email, string $role): bool
    {
        $r = $this->recipientsParsed();
        if ($r === 'all')        return true;
        if ($r === 'role:user')  return $role === 'user';
        if ($r === 'role:admin') return $role === 'admin';
        if (is_array($r)) {
            foreach ($r as $x) {
                if ($x === $email) return true;
                if ((int) $x === $userId) return true;
            }
        }
        return false;
    }
}
