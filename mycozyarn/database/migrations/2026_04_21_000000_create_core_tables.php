<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core business tables — dịch từ database/migrations/migrations/*.sql sang Laravel Blueprint
 * để chạy được trên cả SQL Server lẫn MySQL, và idempotent (bỏ qua bảng đã tồn tại).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('email', 100)->unique();
                $table->string('password', 255);
                $table->string('phone', 20)->nullable();
                $table->text('address')->nullable();
                $table->string('avatar', 255)->nullable();
                $table->string('role', 20)->default('user');        // user | admin
                $table->string('status', 20)->default('active');    // active | blocked
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 150)->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('name', 255);
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->integer('stock_quantity')->default(0);
                $table->string('thumbnail', 255)->nullable();
                $table->string('status', 20)->default('active');    // active | inactive
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('image_url', 255);
                $table->boolean('is_primary')->default(false);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->integer('quantity')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('total_amount', 10, 2);
                $table->text('shipping_address');
                $table->string('payment_method', 20)->default('cod');    // cod | online | bank | momo
                $table->string('payment_status', 20)->default('pending'); // pending | paid | failed
                $table->string('status', 20)->default('pending');         // pending | confirmed | shipping | delivered | cancelled
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->integer('quantity');
                $table->decimal('price', 10, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating'); // 1..5, validate ở app layer
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('content');
                $table->boolean('is_read')->default(false);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // Reverse order vì FK constraints
        Schema::dropIfExists('messages');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};
