<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
    ];

    // Cart thuộc về User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Cart có nhiều CartItem
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
