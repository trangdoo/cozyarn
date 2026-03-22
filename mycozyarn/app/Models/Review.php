<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
    ];

    // Review thuộc về User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Review thuộc về Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
