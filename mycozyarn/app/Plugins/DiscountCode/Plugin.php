<?php

namespace App\Plugins\DiscountCode;

use App\Plugin\Hook;
use App\Plugin\Plugin as BasePlugin;

/**
 * Plugin: Discount Code
 * Minh hoạ filter hook — chiết khấu tổng tiền checkout khi user nhập mã hợp lệ.
 * Hook "checkout.total" (filter): nhận (int $total, array $context) → trả int mới.
 *
 * Mã hợp lệ:
 *   - COZY10  → giảm 10%
 *   - YARN50K → giảm 50.000 ₫ (đơn >= 200.000)
 */
class Plugin extends BasePlugin
{
    public function key(): string         { return 'discount_code'; }
    public function name(): string        { return 'Discount Code'; }
    public function description(): string { return 'Cho phép user nhập mã giảm giá ở checkout (COZY10, YARN50K).'; }
    public function version(): string     { return '1.0.0'; }
    public function author(): string      { return 'CozyYarn Team'; }

    private const CODES = [
        'COZY10'  => ['type' => 'percent', 'value' => 10,    'label' => 'Giảm 10% toàn bộ đơn'],
        'YARN50K' => ['type' => 'flat',    'value' => 50000, 'label' => 'Giảm 50.000 ₫ (đơn từ 200.000 ₫)'],
    ];

    public function boot(): void
    {
        Hook::listen('checkout.total', function (int $total, array $ctx = []): int {
            $code = strtoupper(trim((string) ($ctx['code'] ?? '')));
            if ($code === '' || !isset(self::CODES[$code])) return $total;
            $rule = self::CODES[$code];

            if ($rule['type'] === 'percent') {
                $total -= (int) floor($total * $rule['value'] / 100);
            } elseif ($rule['type'] === 'flat') {
                if ($total >= 200000) $total -= (int) $rule['value'];
            }
            return max(0, $total);
        }, priority: 10);

        // Hook render: hiển thị danh sách mã có sẵn trên trang checkout.
        Hook::listen('checkout.payment_extra', function (): string {
            $rows = '';
            foreach (self::CODES as $code => $rule) {
                $rows .= sprintf(
                    '<li><code style="background:#fde4ee;padding:2px 8px;border-radius:6px">%s</code> — %s</li>',
                    htmlspecialchars($code),
                    htmlspecialchars($rule['label']),
                );
            }
            return <<<HTML
                <div style="margin-top:10px;padding:12px;border:1px dashed #f5d6e3;border-radius:10px;background:#fff7fb">
                    <strong style="font-size:13px;color:#b55a82">🎟️ Mã giảm giá</strong>
                    <ul style="margin:6px 0 0 18px;font-size:13px;line-height:1.7;color:#7f4e63">{$rows}</ul>
                    <small style="display:block;margin-top:4px;color:#a07688">Nhập mã vào ô "Ghi chú" — checkout sẽ tự áp dụng.</small>
                </div>
            HTML;
        }, priority: 10);
    }
}
