<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_desc',
        'price',
        'old_price',
        'stock_quantity',
        'unit',
        'tag',
        'thumbnail',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'old_price'      => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    /** Sử dụng slug làm route key cho mọi route binding của Product. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Alias category_slug → category->slug (chỉ là computed, không phải DB column),
     * tiện cho view legacy dùng key 'category_slug'.
     */
    protected function categorySlug(): Attribute
    {
        return Attribute::get(fn () => $this->category?->slug);
    }
}
