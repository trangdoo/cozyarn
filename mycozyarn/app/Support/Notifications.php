<?php

namespace App\Support;

use App\Support\OrderTimeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Notifications — session-based cho demo.
 * Lưu ở session('notifications') keyed by id.
 * Mỗi notification: id, user_id, type (order|promo), title, content, link, icon, is_read, created_at, meta.
 */
class Notifications
{
    private const ORDER_STAGE_TEMPLATES = [
        'placed' => [
            'title' => 'Đã tiếp nhận đơn #:id',
            'body'  => 'Cảm ơn bạn đã đặt hàng! Shop đang xem xét và sẽ xác nhận sớm.',
            'icon'  => 'order-placed',
        ],
        'pending' => [
            'title' => 'Đơn #:id đang chờ xác nhận',
            'body'  => 'Shop sẽ kiểm tra và xác nhận đơn trong thời gian sớm nhất.',
            'icon'  => 'order-placed',
        ],
        'confirmed' => [
            'title' => 'Đơn #:id đã được xác nhận',
            'body'  => 'Shop đang đóng gói — chuẩn bị bàn giao cho đơn vị vận chuyển.',
            'icon'  => 'order-confirmed',
        ],
        'shipping' => [
            'title' => 'Đơn #:id đang được giao',
            'body'  => 'Đơn hàng đang trên đường đến bạn — chú ý điện thoại nhé!',
            'icon'  => 'order-shipping',
        ],
        'delivered' => [
            'title' => 'Đơn #:id đã giao thành công',
            'body'  => 'Hãy kiểm tra và xác nhận nhận hàng để hoàn tất đơn.',
            'icon'  => 'order-delivered',
        ],
    ];

    /**
     * Đồng bộ notifications: seed promo + tạo notification cho mỗi stage đã pass của mỗi đơn.
     * Gọi trước khi hiển thị danh sách (ở controller index).
     */
    public static function syncForUser(?int $userId = null): void
    {
        $userId ??= Auth::id();
        if (!$userId) return;

        $all = session('notifications', []);
        self::seedPromos($all, $userId);
        self::syncOrderTimelines($all, $userId);

        session(['notifications' => $all]);
    }

    /**
     * Push một notification mới (dùng khi user làm action như huỷ đơn, yêu cầu trả hàng).
     */
    public static function push(array $attrs): void
    {
        $userId = $attrs['user_id'] ?? Auth::id();
        if (!$userId) return;

        $all = session('notifications', []);
        $id  = $attrs['id'] ?? ('NOTIF' . strtoupper(Str::random(8)));

        $all[$id] = [
            'id'         => $id,
            'user_id'    => $userId,
            'type'       => $attrs['type']    ?? 'order',
            'title'      => $attrs['title']   ?? 'Thông báo',
            'content'    => $attrs['content'] ?? '',
            'link'       => $attrs['link']    ?? null,
            'icon'       => $attrs['icon']    ?? 'info',
            'is_read'    => $attrs['is_read'] ?? false,
            'created_at' => $attrs['created_at'] ?? now()->toDateTimeString(),
            'meta'       => $attrs['meta']    ?? [],
        ];

        session(['notifications' => $all]);
    }

    /**
     * Lấy notifications của user (sắp xếp mới → cũ).
     */
    public static function forUser(?int $userId = null): array
    {
        $userId ??= Auth::id();
        if (!$userId) return [];

        $all  = session('notifications', []);
        $mine = array_filter($all, fn($n) => ($n['user_id'] ?? null) === $userId);
        uasort($mine, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $mine;
    }

    /** Đếm số notification chưa đọc — dùng cho badge trên header. */
    public static function unreadCount(?int $userId = null): int
    {
        $userId ??= Auth::id();
        if (!$userId) return 0;

        $all = session('notifications', []);
        $c   = 0;
        foreach ($all as $n) {
            if (($n['user_id'] ?? null) === $userId && empty($n['is_read'])) $c++;
        }
        return $c;
    }

    public static function markRead(string $id): void
    {
        $all = session('notifications', []);
        if (!isset($all[$id])) return;
        if (($all[$id]['user_id'] ?? null) !== Auth::id()) return;

        $all[$id]['is_read'] = true;
        session(['notifications' => $all]);
    }

    public static function markAllRead(?int $userId = null): void
    {
        $userId ??= Auth::id();
        if (!$userId) return;

        $all = session('notifications', []);
        foreach ($all as $id => $n) {
            if (($n['user_id'] ?? null) === $userId) {
                $all[$id]['is_read'] = true;
            }
        }
        session(['notifications' => $all]);
    }

    /* ═══════════════════════════ private helpers ═══════════════════════════ */

    /**
     * Seed một số promo mặc định cho mỗi user (chỉ seed 1 lần — check bằng flag session).
     */
    private static function seedPromos(array &$all, int $userId): void
    {
        $flagKey = "notif_promo_seeded_user_{$userId}";
        if (session($flagKey)) return;

        $promos = [
            [
                'title'    => '🎉 Giảm 20% toàn bộ len sợi!',
                'content'  => 'Nhập mã COZY20 khi thanh toán — áp dụng cho đơn từ 300k. HSD 30/04.',
                'link'     => '/shop/len-soi',
                'cta'      => 'Mua len ngay',
                'icon'     => 'promo-discount',
                'banner'   => '/images/1.jpg',
                'code'     => 'COZY20',
                'valid_until' => '30/04/2026',
                'details'  => [
                    'Chào mừng những đôi tay khéo léo của bạn đến với ưu đãi lớn nhất tháng này! CozyYarn đang tặng bạn voucher giảm giá 20% cho toàn bộ danh mục len sợi — từ len cotton pastel dịu nhẹ cho đến len mohair ánh kim sang trọng.',
                    'Điều kiện áp dụng: đơn hàng có tổng giá trị từ 300.000đ trở lên. Mỗi tài khoản được sử dụng tối đa 1 lần trong thời gian khuyến mãi. Không áp dụng đồng thời với các mã giảm giá khác.',
                    'Cách dùng: chọn sản phẩm len sợi yêu thích, thêm vào giỏ, ở bước thanh toán nhập mã **COZY20** — hệ thống sẽ tự động trừ 20% giá trị đơn. Đơn giản vậy thôi!',
                ],
                'highlights' => [
                    'Áp dụng cho toàn bộ danh mục len sợi',
                    'Giảm tối đa 200.000đ/đơn',
                    'Không giới hạn số lượng sản phẩm',
                    'Tích luỹ điểm thành viên bình thường',
                ],
            ],
            [
                'title'    => '🚚 Freeship đơn từ 500k',
                'content'  => 'Mua thêm bất kỳ sản phẩm nào để đạt mốc miễn phí vận chuyển toàn quốc.',
                'link'     => '/shop',
                'cta'      => 'Khám phá shop',
                'icon'     => 'promo-ship',
                'banner'   => '/images/2.jpg',
                'code'     => null,
                'valid_until' => 'Không giới hạn',
                'details'  => [
                    'Chương trình miễn phí vận chuyển toàn quốc dành cho tất cả đơn hàng có tổng giá trị (sau giảm giá) từ 500.000đ trở lên. Giao hàng nhanh 2–4 ngày làm việc, đóng gói cẩn thận như quà tặng.',
                    'Bạn có thể gom nhiều sản phẩm khác loại cùng một đơn để tận dụng ưu đãi — kit starter, len sợi, kim móc, phụ kiện... tất cả đều tính chung để đạt mốc.',
                    'Ưu đãi áp dụng song song với mọi voucher giảm giá hiện có. Không cần nhập mã, hệ thống tự động áp dụng khi đơn đủ điều kiện.',
                ],
                'highlights' => [
                    'Áp dụng toàn quốc (63 tỉnh/thành)',
                    'Tự động áp dụng khi đủ 500k',
                    'Kết hợp được với voucher khác',
                    'Giao 2–4 ngày làm việc',
                ],
            ],
            [
                'title'    => '🧶 Bộ Starter Kit mới ra mắt',
                'content'  => 'Kit gấu bông, khăn quàng, túi xách — đầy đủ dụng cụ cho người mới.',
                'link'     => '/shop/starter-kit',
                'cta'      => 'Xem bộ kit',
                'icon'     => 'promo-new',
                'banner'   => '/images/3.jpg',
                'code'     => null,
                'valid_until' => 'Số lượng có hạn',
                'details'  => [
                    'CozyYarn vừa cho ra mắt bộ Starter Kit dành cho người mới bắt đầu hành trình đan móc — với 3 lựa chọn chủ đề: Gấu bông, Khăn quàng, và Túi xách nhỏ. Mỗi bộ đều đã bao gồm len, kim, hướng dẫn chi tiết từng bước.',
                    'Phù hợp cho người hoàn toàn mới lần đầu cầm kim, hoặc các bạn muốn tặng quà cho bạn bè yêu thích handmade. Video hướng dẫn kèm theo QR code trên hộp, xem được ngay trên điện thoại.',
                    'Đặc biệt: nếu bạn mua combo 2 kit trở lên sẽ được tặng thêm 1 cuộn len cotton pastel làm quà. Ưu đãi giới hạn trong 200 đơn đầu tiên.',
                ],
                'highlights' => [
                    'Đầy đủ len + kim + hướng dẫn',
                    'Video QR hướng dẫn từng bước',
                    '3 chủ đề: gấu bông / khăn / túi',
                    'Mua combo tặng len cotton pastel',
                ],
            ],
        ];

        foreach ($promos as $i => $p) {
            $id = "PROMO" . strtoupper(Str::random(6));
            $all[$id] = [
                'id'         => $id,
                'user_id'    => $userId,
                'type'       => 'promo',
                'title'      => $p['title'],
                'content'    => $p['content'],
                'link'       => $p['link'],
                'icon'       => $p['icon'],
                'is_read'    => false,
                'created_at' => now()->subMinutes(15 + $i * 10)->toDateTimeString(),
                'meta'       => [
                    'cta'         => $p['cta'],
                    'banner'      => $p['banner'],
                    'code'        => $p['code'],
                    'valid_until' => $p['valid_until'],
                    'details'     => $p['details'],
                    'highlights'  => $p['highlights'],
                ],
            ];
        }

        session([$flagKey => true]);
    }

    /**
     * Với mỗi đơn của user, sinh notification cho mỗi stage timeline đã đạt.
     * ID deterministic: "ORDER-{orderId}-{stage}" → không tạo trùng.
     */
    private static function syncOrderTimelines(array &$all, int $userId): void
    {
        $orders = session('orders', []);
        foreach ($orders as $order) {
            if (($order['user_id'] ?? null) !== $userId) continue;
            $orderId = $order['id'] ?? null;
            if (!$orderId) continue;

            $timeline = OrderTimeline::compute($order);
            $reachedSteps = [];
            foreach ($timeline['steps'] as $step) {
                if (($step['is_done'] ?? false) || ($step['is_current'] ?? false)) {
                    $reachedSteps[] = $step['key'];
                }
            }

            foreach ($reachedSteps as $stage) {
                if (!isset(self::ORDER_STAGE_TEMPLATES[$stage])) continue;

                $notifId = "ORDER-{$orderId}-{$stage}";
                if (isset($all[$notifId])) continue; // đã tạo rồi

                $tpl = self::ORDER_STAGE_TEMPLATES[$stage];
                $all[$notifId] = [
                    'id'         => $notifId,
                    'user_id'    => $userId,
                    'type'       => 'order',
                    'title'      => str_replace(':id', $orderId, $tpl['title']),
                    'content'    => $tpl['body'],
                    'link'       => "/don-hang/{$orderId}",
                    'icon'       => $tpl['icon'],
                    'is_read'    => false,
                    'created_at' => $order['created_at'] ?? now()->toDateTimeString(),
                    'meta'       => ['order_id' => $orderId, 'stage' => $stage],
                ];
            }
        }
    }
}
