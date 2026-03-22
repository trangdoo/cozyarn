<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_amount',
        'shipping_address',
        'payment_method',
        'payment_status',
        'status',
        'note',
    ];

    // Order thuộc về User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Order có nhiều OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
