<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mở rộng `orders` + `order_items` để toàn bộ data đơn hàng được lưu DB (thay vì
 * session-based như mô hình demo cũ). Mục tiêu: admin nhìn được mọi đơn từ mọi
 * session, không bị mất khi clear session.
 *
 * Orders: thêm customer info (tách field thay vì lồng trong shipping_address),
 * breakdown tiền (subtotal/shipping_fee/discount), status_history (json), các
 * timestamp transition (paid_at/cancelled_at/...).
 *
 * Order_items: drop FK products (vì shop hiện dùng static config resources/shop.php),
 * thêm snapshot fields để mỗi item tự đủ thông tin hiển thị (không phụ thuộc products DB).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name', 120)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('orders', 'province')) {
                $table->string('province', 80)->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('orders', 'district')) {
                $table->string('district', 80)->nullable()->after('province');
            }
            if (!Schema::hasColumn('orders', 'address_line')) {
                $table->string('address_line', 300)->nullable()->after('district');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'shipping_fee')) {
                $table->decimal('shipping_fee', 12, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'discount_code')) {
                $table->string('discount_code', 40)->nullable()->after('discount');
            }
            if (!Schema::hasColumn('orders', 'status_history')) {
                $table->json('status_history')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status_history');
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('orders', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('orders', 'cancel_reason')) {
                $table->string('cancel_reason', 500)->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('orders', 'return_requested_at')) {
                $table->timestamp('return_requested_at')->nullable()->after('cancel_reason');
            }
            if (!Schema::hasColumn('orders', 'return_reason')) {
                $table->string('return_reason', 500)->nullable()->after('return_requested_at');
            }
            if (!Schema::hasColumn('orders', 'return_evidence')) {
                // {images: [...], video: '...'} — paths cho ảnh + video bằng chứng trả hàng.
                $table->json('return_evidence')->nullable()->after('return_reason');
            }
            if (!Schema::hasColumn('orders', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('return_evidence');
            }
        });

        // Order items: drop FK to products + relax + add snapshot cols.
        // Thử cả tên FK mặc định của Laravel + tên đặt thủ công (FK_order_items_products
        // sinh ra từ SQL gốc khi DB tạo bằng tool ngoài Laravel).
        foreach (['order_items_product_id_foreign', 'FK_order_items_products'] as $fkName) {
            try {
                Schema::table('order_items', function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName);
                });
            } catch (\Throwable $e) {
                // FK không tồn tại với tên đó — thử tên khác.
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->change();
            }
            if (!Schema::hasColumn('order_items', 'category_slug')) {
                $table->string('category_slug', 80)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('order_items', 'product_slug')) {
                $table->string('product_slug', 120)->nullable()->after('category_slug');
            }
            if (!Schema::hasColumn('order_items', 'name')) {
                $table->string('name', 200)->nullable()->after('product_slug');
            }
            if (!Schema::hasColumn('order_items', 'image')) {
                $table->string('image', 300)->nullable()->after('name');
            }
            if (!Schema::hasColumn('order_items', 'variant')) {
                $table->string('variant', 80)->nullable()->after('image');
            }
            if (!Schema::hasColumn('order_items', 'size')) {
                $table->string('size', 30)->nullable()->after('variant');
            }
            if (!Schema::hasColumn('order_items', 'item_key')) {
                $table->string('item_key', 255)->nullable()->after('size');
                $table->index(['order_id', 'item_key']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['item_key', 'size', 'variant', 'image', 'name', 'product_slug', 'category_slug'] as $col) {
                if (Schema::hasColumn('order_items', $col)) {
                    try { $table->dropIndex(['order_id', 'item_key']); } catch (\Throwable $e) {}
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'refunded_at', 'return_reason', 'return_requested_at',
                'cancelled_at', 'delivered_at', 'shipped_at', 'confirmed_at', 'paid_at',
                'status_history', 'discount_code', 'discount', 'shipping_fee', 'subtotal',
                'address_line', 'district', 'province', 'customer_phone', 'customer_name',
            ] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
