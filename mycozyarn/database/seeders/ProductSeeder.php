<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed sản phẩm từ resources/shop.php.
     * Idempotent — dùng updateOrCreate theo slug.
     */
    public function run(): void
    {
        $shop = require resource_path('shop.php');
        $categoriesBySlug = Category::pluck('id', 'slug');
        $count = 0;

        foreach ($shop['products'] ?? [] as $catSlug => $list) {
            $categoryId = $categoriesBySlug[$catSlug] ?? null;
            if (!$categoryId) {
                $this->command?->warn("Bỏ qua: chưa có category '{$catSlug}'. Hãy chạy CategorySeeder trước.");
                continue;
            }

            foreach ($list as $p) {
                $stock = $p['quantity']
                    ?? array_sum(array_column($p['variants'] ?? [], 'stock'))
                    ?: 0;

                Product::updateOrCreate(
                    ['slug' => $p['slug']],
                    [
                        'category_id'    => $categoryId,
                        'name'           => (string) $p['name'],
                        'description'    => $p['desc'] ?? null,
                        'short_desc'     => $p['shortDesc'] ?? null,
                        'price'          => (int) ($p['price'] ?? 0),
                        'old_price'      => isset($p['oldPrice']) && $p['oldPrice'] !== null ? (int) $p['oldPrice'] : null,
                        'stock_quantity' => (int) $stock,
                        'unit'           => $p['unit'] ?? 'cuộn',
                        'tag'            => $p['tag'] ?? null,
                        'thumbnail'      => $p['image'] ?? '/images/1.jpg',
                        'status'         => $p['status'] ?? 'active',
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("Seeded {$count} products.");
    }
}
