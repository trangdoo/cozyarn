<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock_quantity',
        'thumbnail',
        'status',
    ];
// trỏ sản phẩm về catego
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // định nghĩa 1 nhiều cho item
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
