<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Single source of truth cho đơn hàng — đọc/ghi DB và xuất ra "shape" array tương
 * thích với views cũ (đã dùng nhiều khoá kiểu camelCase/lowercase quen thuộc).
 *
 * Mục tiêu: thay thế hoàn toàn `session('orders', [])` ở cả phía user lẫn admin.
 * Khi cần shape array → gọi self::toArray($orderModel).
 */
class OrderStore
{
    /* ═══════════════════════════ READ ═══════════════════════════ */

    public static function find(int|string $id): ?array
    {
        $model = Order::with('items')->find((int) $id);
        return $model ? self::toArray($model) : null;
    }

    public static function findForUser(int|string $id, int $userId): ?array
    {
        $model = Order::with('items')->where('id', (int) $id)->where('user_id', $userId)->first();
        return $model ? self::toArray($model) : null;
    }

    /** Tất cả đơn (cho admin) — sort created_at desc. */
    public static function all(): array
    {
        return Order::with('items')->orderByDesc('created_at')->get()
            ->map(fn ($o) => self::toArray($o))->all();
    }

    /** Đơn của 1 user — sort created_at desc. */
    public static function forUser(int $userId): array
    {
        return Order::with('items')->where('user_id', $userId)->orderByDesc('created_at')->get()
            ->map(fn ($o) => self::toArray($o))->all();
    }

    /* ═══════════════════════════ WRITE ═══════════════════════════ */

    /**
     * Tạo đơn mới từ checkout data + items (đã chọn ở giỏ).
     * $data chứa: name, phone, province, district, address, note, payment.
     * $items là array map key => [name, image, price, qty, variant, size, category, slug, key].
     * $totals = [subtotal, shippingFee, total, discount, discount_code].
     */
    public static function create(int $userId, array $data, array $items, array $totals): Order
    {
        return DB::transaction(function () use ($userId, $data, $items, $totals) {
            $order = Order::create([
                'user_id'          => $userId,
                'customer_name'    => $data['name']     ?? null,
                'customer_phone'   => $data['phone']    ?? null,
                'province'         => $data['province'] ?? null,
                'district'         => $data['district'] ?? null,
                'address_line'     => $data['address']  ?? null,
                'shipping_address' => trim(($data['address'] ?? '') . ', ' . ($data['district'] ?? '') . ', ' . ($data['province'] ?? ''), ', '),
                'subtotal'         => $totals['subtotal']      ?? 0,
                'shipping_fee'     => $totals['shippingFee']   ?? 0,
                'discount'         => $totals['discount']      ?? 0,
                'discount_code'    => $totals['discount_code'] ?? null,
                'total_amount'    => $totals['total']         ?? 0,
                'payment_method'   => $data['payment'] ?? 'cod',
                'payment_status'   => 'pending',
                'status'           => 'pending',
                'note'              => $data['note'] ?? null,
                'status_history'   => [],
            ]);

            foreach ($items as $key => $it) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => null,
                    'category_slug' => $it['category'] ?? null,
                    'product_slug'  => $it['slug']     ?? null,
                    'name'          => $it['name']     ?? '',
                    'image'         => $it['image']    ?? null,
                    'variant'       => $it['variant']  ?? null,
                    'size'          => $it['size']     ?? null,
                    'item_key'      => $it['key']      ?? (string) $key,
                    'quantity'      => (int) ($it['qty'] ?? 1),
                    'price'         => (int) ($it['price'] ?? 0),
                ]);
            }

            return $order->load('items');
        });
    }

    /** Push 1 dòng history vào status_history. */
    public static function pushHistory(int|string $orderId, string $from, string $to, string $by, string $note = ''): void
    {
        $order = Order::find((int) $orderId);
        if (!$order) return;

        $history = $order->status_history ?? [];
        $history[] = [
            'from' => $from,
            'to'   => $to,
            'by'   => $by,
            'at'   => now()->toDateTimeString(),
            'note' => $note,
        ];
        $order->status_history = $history;
        $order->save();
    }

    /**
     * Chuyển trạng thái + ghi history + cập nhật timestamp tương ứng.
     * Return true nếu transition thực sự xảy ra (allowed), false nếu không.
     */
    public static function transition(
        int|string $orderId,
        string $to,
        array $allowedFrom,
        string $by,
        string $note = '',
    ): bool {
        $order = Order::find((int) $orderId);
        if (!$order) return false;

        $from = (string) $order->status;
        if (!\in_array($from, $allowedFrom, true)) return false;

        $history = $order->status_history ?? [];
        $history[] = [
            'from' => $from,
            'to'   => $to,
            'by'   => $by,
            'at'   => now()->toDateTimeString(),
            'note' => $note,
        ];

        $patch = ['status' => $to, 'status_history' => $history];
        switch ($to) {
            case 'confirmed': $patch['confirmed_at']        = now(); break;
            case 'shipping':  $patch['shipped_at']          = now(); break;
            case 'delivered': $patch['delivered_at']        = now(); break;
            case 'received':  $patch['received_at']         = now(); break;
            case 'cancelled': $patch['cancelled_at']        = now(); break;
            case 'returned':  $patch['refunded_at']         = now(); break;
            case 'return_requested': $patch['return_requested_at'] = now(); break;
        }
        $order->update($patch);
        return true;
    }

    /** Đánh dấu paid + push history. Trả về true nếu thực sự flip pending → paid. */
    public static function markPaid(int|string $orderId, string $by = 'SePay webhook'): bool
    {
        $affected = Order::where('id', (int) $orderId)
            ->where('payment_status', 'pending')
            ->update([
                'payment_status' => 'paid',
                'paid_at'        => now(),
            ]);

        if ($affected > 0) {
            self::pushHistory($orderId, 'pending', 'paid', $by, 'Đã nhận thanh toán');
        }
        return $affected > 0;
    }

    /* ═══════════════════════════ PRESENT ═══════════════════════════ */

    /**
     * Convert Eloquent Order (+items) → array shape mà views/controllers cũ đã dùng
     * (snake_case + một số camelCase quen thuộc: shippingFee, total, name, phone, ...).
     */
    public static function toArray(Order $order): array
    {
        $items = $order->items->map(fn ($it) => [
            'key'      => $it->item_key,
            'category' => $it->category_slug,
            'slug'     => $it->product_slug,
            'name'     => $it->name,
            'image'    => $it->image,
            'price'    => (int) $it->price,
            'qty'      => (int) $it->quantity,
            'variant'  => $it->variant,
            'size'     => $it->size,
        ])->all();

        $fmt = fn ($v) => $v instanceof Carbon ? $v->toDateTimeString() : ($v ?? null);

        return [
            'id'             => (string) $order->id,
            'user_id'        => $order->user_id,
            'items'          => $items,
            'subtotal'       => (int) $order->subtotal,
            'shippingFee'    => (int) $order->shipping_fee,
            'discount'       => (int) $order->discount,
            'discount_code'  => $order->discount_code ?? '',
            'total'          => (int) $order->total_amount,
            'name'           => $order->customer_name,
            'phone'          => $order->customer_phone,
            'province'       => $order->province,
            'district'       => $order->district,
            'address'        => $order->address_line,
            'note'           => $order->note ?? '',
            'payment'        => $order->payment_method,
            'payment_status' => $order->payment_status,
            'status'         => $order->status,
            'status_history' => $order->status_history ?? [],
            'created_at'     => $fmt($order->created_at),
            'paid_at'        => $fmt($order->paid_at),
            'confirmed_at'   => $fmt($order->confirmed_at),
            'shipped_at'     => $fmt($order->shipped_at),
            'delivered_at'   => $fmt($order->delivered_at),
            'cancelled_at'   => $fmt($order->cancelled_at),
            'return_requested_at' => $fmt($order->return_requested_at),
            'return_reason'  => $order->return_reason,
            'refunded_at'    => $fmt($order->refunded_at),
        ];
    }
}
