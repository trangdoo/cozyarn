<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_url',
        'is_primary',
    ];

    // ProductImage thuộc về Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
