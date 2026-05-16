<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notif_key',
        'type',
        'title',
        'content',
        'link',
        'is_read',
        'read_at',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
        'meta'       => 'array',
    ];
}
