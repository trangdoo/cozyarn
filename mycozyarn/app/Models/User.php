<?php

namespace App\Models;

use App\Support\ClientPasswordNormalizer;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mutator password — chuẩn hoá theo flow client-hash:
     *   1) Nếu input đã là SHA-256 hex (client đã hash) → giữ nguyên.
     *   2) Nếu là plaintext (client không hash, vd: seeder gửi sai)
     *      → hash SHA-256 ở server để cùng pipeline.
     *   3) Cuối cùng bcrypt giá trị đó để lưu DB.
     *
     * Tránh hash 2 lần: nếu input đã là bcrypt sẵn ($2y$...) thì bỏ qua bước 3.
     */
    public function setPasswordAttribute(string $value): void
    {
        if (str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$') || str_starts_with($value, '$2b$')) {
            $this->attributes['password'] = $value;
            return;
        }

        $normalized = ClientPasswordNormalizer::normalize($value);
        $this->attributes['password'] = Hash::make($normalized);
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
