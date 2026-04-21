<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

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

    /** Ẩn khi serialize (array/JSON). */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Auto-hash password khi gán. */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? 'user') === 'admin';
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
