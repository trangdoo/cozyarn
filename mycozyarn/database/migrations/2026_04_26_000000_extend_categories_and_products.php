<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bổ sung các cột phục vụ UI admin (image, short_desc, old_price, unit, tag).
 * Idempotent — chỉ thêm khi chưa tồn tại để chạy lại không lỗi.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'image')) {
                $table->string('image', 255)->nullable()->after('description');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'short_desc')) {
                $table->string('short_desc', 500)->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'old_price')) {
                $table->decimal('old_price', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 30)->default('cuộn')->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'tag')) {
                $table->string('tag', 30)->nullable()->after('unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['tag', 'unit', 'old_price', 'short_desc'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
