<?php

namespace App\Support;

/**
 * Giỏ hàng session-based. Dữ liệu lưu ở session('cart') dạng mảng
 * với khóa là key duy nhất (kết hợp category/slug/variant/size).
 */
class Cart
{
    public const SESSION_KEY = 'cart';

    /** Lấy mảng item hiện có trong giỏ. */
    public static function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /** Tổng số sản phẩm (cộng theo qty). */
    public static function count(): int
    {
        return array_sum(array_column(self::items(), 'qty'));
    }

    /** Tổng tiền (VND). */
    public static function subtotal(): int
    {
        $total = 0;
        foreach (self::items() as $item) {
            $total += ((int) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0));
        }
        return $total;
    }

    /** Sinh key duy nhất cho item theo variant/size. */
    public static function makeKey(string $category, string $slug, ?string $variant = null, ?string $size = null): string
    {
        return sprintf('%s|%s|%s|%s', $category, $slug, $variant ?? '', $size ?? '');
    }

    /** Thêm / tăng số lượng. */
    public static function add(string $key, array $data, int $qty = 1): void
    {
        $cart = self::items();
        $qty  = max(1, $qty);

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [...$data, 'key' => $key, 'qty' => $qty];
        }

        session([self::SESSION_KEY => $cart]);
    }

    /** Cập nhật số lượng. qty <= 0 => xoá. */
    public static function update(string $key, int $qty): void
    {
        $cart = self::items();
        if (!isset($cart[$key])) return;

        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['qty'] = $qty;
        }
        session([self::SESSION_KEY => $cart]);
    }

    public static function remove(string $key): void
    {
        $cart = self::items();
        unset($cart[$key]);
        session([self::SESSION_KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
