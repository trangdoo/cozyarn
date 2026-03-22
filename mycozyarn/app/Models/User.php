<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'role',
        'status',
    ];

    // User có nhiều Order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // User có nhiều Review
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // User có một Cart
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
