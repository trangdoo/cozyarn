<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed danh mục từ resources/shop.php.
     * Idempotent — dùng updateOrCreate theo slug.
     */
    public function run(): void
    {
        $shop = require resource_path('shop.php');
        $count = 0;

        foreach ($shop['categories'] ?? [] as $slug => $c) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'        => (string) ($c['name'] ?? $slug),
                    'description' => $c['desc'] ?? null,
                    'image'       => $c['image'] ?? null,
                ]
            );
            $count++;
        }

        $this->command?->info("Seeded {$count} categories.");
    }
}
