<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name', 'customer_phone',
        'province', 'district', 'address_line',
        'shipping_address',
        'subtotal', 'shipping_fee', 'discount', 'discount_code',
        'total_amount',
        'payment_method', 'payment_status',
        'status', 'status_history',
        'note',
        'paid_at', 'confirmed_at', 'shipped_at', 'delivered_at', 'received_at',
        'cancelled_at', 'cancel_reason',
        'return_requested_at', 'return_reason', 'return_evidence', 'refunded_at',
    ];

    protected $casts = [
        'status_history'     => 'array',
        'return_evidence'    => 'array',
        'subtotal'           => 'decimal:2',
        'shipping_fee'       => 'decimal:2',
        'discount'           => 'decimal:2',
        'total_amount'       => 'decimal:2',
        'paid_at'            => 'datetime',
        'confirmed_at'       => 'datetime',
        'shipped_at'         => 'datetime',
        'delivered_at'       => 'datetime',
        'received_at'        => 'datetime',
        'cancelled_at'       => 'datetime',
        'return_requested_at'=> 'datetime',
        'refunded_at'        => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
