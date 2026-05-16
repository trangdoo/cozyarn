<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm DB tables cho usecase Chat & Notification.
 *
 * users.id ở DB hiện tại là INT signed (không phải BIGINT UNSIGNED mặc định của Laravel).
 * Vì vậy các FK đến users đều dùng integer()->unsigned()->nullable() / integer() để khớp type.
 *
 *  - chat_threads          : metadata 1 cuộc hội thoại (shop | product-*)
 *  - messages (+ cols)     : bổ sung thread_id, sender_type, image_url, updated_at
 *  - notifications         : thông báo của user (order/promo/system)
 *  - admin_notifications   : inbox dùng chung cho admin (order_new/message/...)
 *  - broadcasts            : admin broadcast gửi nhiều user
 *  - broadcast_deliveries  : tracking đã gửi tới user nào (chống double-push)
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('chat_threads')) {
            Schema::create('chat_threads', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->string('thread_key', 160);              // 'shop' | 'product-{cat}-{slug}'
                $table->string('title', 200);
                $table->string('subtitle', 300)->nullable();
                $table->string('type', 30)->default('shop');     // shop | product
                $table->json('product_meta')->nullable();        // {slug, category, name, image, price}
                $table->boolean('pinned')->default(false);
                $table->boolean('muted')->default(false);
                $table->timestamp('last_read_by_user')->nullable();
                $table->timestamp('last_read_by_shop')->nullable();
                $table->string('last_preview', 300)->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'thread_key']);
                $table->index(['user_id', 'updated_at']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'thread_id')) {
                $table->unsignedBigInteger('thread_id')->nullable()->after('id');
                $table->foreign('thread_id')->references('id')->on('chat_threads')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('messages', 'sender_type')) {
                $table->string('sender_type', 10)->default('user')->after('sender_id'); // user | shop
            }
            if (!Schema::hasColumn('messages', 'image_url')) {
                $table->string('image_url', 300)->nullable()->after('content');
            }
            if (!Schema::hasColumn('messages', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
            // Cho phép content null (cho message chỉ có ảnh)
            $table->text('content')->nullable()->change();
        });

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('notif_key', 120)->nullable();
                $table->integer('user_id');
                $table->string('type', 30)->default('order');
                $table->string('title', 200);
                $table->text('content')->nullable();
                $table->string('link', 300)->nullable();
                $table->string('icon', 50)->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'is_read']);
                $table->index(['user_id', 'created_at']);
                $table->unique(['user_id', 'notif_key']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('notif_key', 120)->nullable()->unique();
                $table->string('type', 30)->default('system');
                $table->string('title', 200);
                $table->text('content')->nullable();
                $table->string('link', 300)->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['type', 'is_read']);
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('broadcasts')) {
            Schema::create('broadcasts', function (Blueprint $table) {
                $table->id();
                $table->integer('sender_id')->nullable();
                $table->string('type', 30)->default('promo');
                $table->string('title', 200);
                $table->text('content')->nullable();
                $table->string('link', 300)->nullable();
                $table->string('icon', 50)->nullable();
                $table->string('recipients', 500)->default('all');
                $table->json('meta')->nullable();
                $table->timestamp('send_at')->nullable();
                $table->timestamps();

                $table->index('send_at');
                $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('broadcast_deliveries')) {
            Schema::create('broadcast_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('broadcast_id');
                $table->integer('user_id');
                $table->timestamp('delivered_at')->useCurrent();

                $table->unique(['broadcast_id', 'user_id']);
                $table->foreign('broadcast_id')->references('id')->on('broadcasts')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_deliveries');
        Schema::dropIfExists('broadcasts');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('notifications');

        Schema::table('messages', function (Blueprint $table) {
            foreach (['updated_at', 'image_url', 'sender_type'] as $col) {
                if (Schema::hasColumn('messages', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('messages', 'thread_id')) {
                $table->dropForeign(['thread_id']);
                $table->dropColumn('thread_id');
            }
        });

        Schema::dropIfExists('chat_threads');
    }
};
