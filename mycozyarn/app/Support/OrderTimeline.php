<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Tính stage hiện tại của đơn hàng dựa trên thời gian đã trôi qua kể từ khi đặt.
 * Chỉ dùng cho demo (session-based orders). Khi có bảng orders thật + admin panel
 * cập nhật trạng thái, thay bằng cách đọc field 'status' trực tiếp.
 */
class OrderTimeline
{
    /** 5 bước mặc định. Mỗi bước: key + nhãn + icon + threshold (phút kể từ created_at). */
    public const STEPS = [
        ['key' => 'placed',    'label' => 'Đã đặt hàng',    'threshold' => 0],
        ['key' => 'pending',   'label' => 'Chờ xác nhận',   'threshold' => 0],
        ['key' => 'confirmed', 'label' => 'Chờ lấy hàng',   'threshold' => 1],
        ['key' => 'shipping',  'label' => 'Chờ giao hàng',  'threshold' => 5],
        ['key' => 'delivered', 'label' => 'Giao thành công','threshold' => 15],
    ];

    /**
     * @return array{current: int, elapsed: int, steps: array}
     * current: index (0..4) của step hiện tại
     * elapsed: số phút đã trôi
     * steps:   mảng steps có 'is_done' và 'is_current' cho từng step
     */
    public static function compute(array $order): array
    {
        $createdAt = Carbon::parse($order['created_at'] ?? now());
        $elapsed   = (int) $createdAt->diffInMinutes(now());

        // Nếu status = 'cancelled' thì trả về flow huỷ (không dùng time-based)
        if (($order['status'] ?? '') === 'cancelled') {
            return [
                'current'   => 0,
                'elapsed'   => $elapsed,
                'cancelled' => true,
                'steps'     => [],
            ];
        }

        $current = 0;
        foreach (self::STEPS as $i => $step) {
            if ($elapsed >= $step['threshold']) $current = $i;
        }

        $steps = [];
        foreach (self::STEPS as $i => $step) {
            $steps[] = [
                ...$step,
                'is_done'    => $i < $current,
                'is_current' => $i === $current,
            ];
        }

        return [
            'current'   => $current,
            'elapsed'   => $elapsed,
            'cancelled' => false,
            'steps'     => $steps,
        ];
    }

    /** Tên stage hiện tại dạng text (để hiển thị trong list đơn). */
    public static function currentLabel(array $order): string
    {
        if (($order['status'] ?? '') === 'cancelled') return 'Đã huỷ';
        $t = self::compute($order);
        return self::STEPS[$t['current']]['label'] ?? 'Đang xử lý';
    }

    /** Key stage hiện tại — dùng cho badge color mapping. */
    public static function currentKey(array $order): string
    {
        if (($order['status'] ?? '') === 'cancelled') return 'cancelled';
        $t = self::compute($order);
        return self::STEPS[$t['current']]['key'] ?? 'pending';
    }
}
