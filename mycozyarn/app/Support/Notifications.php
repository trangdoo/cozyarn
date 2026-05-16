<?php

namespace App\Support;

use App\Models\Notification;
use App\Support\OrderTimeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Notifications — DB-backed.
 * Public API (forUser/unreadCount/markRead/markAllRead/push/syncForUser) trả về array shape
 * giống session-based cũ: id, user_id, type, title, content, link, icon, is_read, created_at, meta.
 * Views/Controllers cũ không cần đổi shape.
 *
 * notif_key (deterministic): 'ORDER-{orderId}-{stage}', 'PROMO-SEED-{i}', 'BROADCAST-{id}'
 *   → unique(user_id, notif_key) chống trùng. Không có notif_key thì lưu null, id = DB PK.
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
     * Sync: seed promo (1 lần/user) + tạo notification cho mỗi stage đơn đã pass +
     * pull từ BroadcastQueue.
     */
    public static function syncForUser(?int $userId = null): void
    {
        $userId ??= Auth::id();
        if (!$userId) return;

        self::seedPromos($userId);
        self::syncOrderTimelines($userId);
        self::syncBroadcastQueue($userId);
    }

    /**
     * Tạo notification mới (dùng cho action user-triggered: cancel order, return request...).
     * Nếu có notif_key trùng (user_id, notif_key) thì skip.
     */
    public static function push(array $attrs): void
    {
        $userId = $attrs['user_id'] ?? Auth::id();
        if (!$userId) return;

        $notifKey = $attrs['id'] ?? $attrs['notif_key'] ?? null;
        // Nếu 'id' truyền vào trông không phải số → dùng làm notif_key (back-compat string ids cũ).
        if ($notifKey !== null && !is_numeric($notifKey)) {
            $exists = Notification::where('user_id', $userId)->where('notif_key', $notifKey)->exists();
            if ($exists) return;
        } else {
            $notifKey = null;
        }

        Notification::create([
            'notif_key'  => $notifKey,
            'user_id'    => $userId,
            'type'       => $attrs['type']    ?? 'order',
            'title'      => $attrs['title']   ?? 'Thông báo',
            'content'    => $attrs['content'] ?? '',
            'link'       => $attrs['link']    ?? null,
            'icon'       => $attrs['icon']    ?? 'info',
            'is_read'    => $attrs['is_read'] ?? false,
            'created_at' => $attrs['created_at'] ?? now(),
            'meta'       => $attrs['meta']    ?? [],
        ]);
    }

    /**
     * Lấy notifications của user, mới → cũ. Trả về array keyed by id (PK hoặc notif_key).
     */
    public static function forUser(?int $userId = null): array
    {
        $userId ??= Auth::id();
        if (!$userId) return [];

        $rows = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $key = $row->notif_key ?: (string) $row->id;
            $out[$key] = self::toArray($row);
        }
        return $out;
    }

    /**
     * Tìm 1 notification của user theo id (PK) hoặc notif_key. Trả null nếu không thấy/không phải của user.
     */
    public static function find(string $idOrKey, ?int $userId = null): ?array
    {
        $userId ??= Auth::id();
        if (!$userId) return null;

        $q = Notification::where('user_id', $userId);
        $q = is_numeric($idOrKey)
            ? $q->where('id', (int) $idOrKey)
            : $q->where('notif_key', $idOrKey);

        $row = $q->first();
        return $row ? self::toArray($row) : null;
    }

    public static function unreadCount(?int $userId = null): int
    {
        $userId ??= Auth::id();
        if (!$userId) return 0;

        return Notification::where('user_id', $userId)->where('is_read', false)->count();
    }

    public static function markRead(string $idOrKey): void
    {
        $userId = Auth::id();
        if (!$userId) return;

        $q = Notification::where('user_id', $userId);
        $q = is_numeric($idOrKey)
            ? $q->where('id', (int) $idOrKey)
            : $q->where('notif_key', $idOrKey);

        $q->update(['is_read' => true, 'read_at' => now()]);
    }

    public static function markAllRead(?int $userId = null): void
    {
        $userId ??= Auth::id();
        if (!$userId) return;

        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /* ═══════════════════════════ private helpers ═══════════════════════════ */

    private static function toArray($row): array
    {
        $key = $row->notif_key ?: (string) $row->id;
        return [
            'id'         => $key,                                          // back-compat: view dùng $n['id']
            'pk'         => $row->id,                                      // DB PK riêng nếu cần
            'notif_key'  => $row->notif_key,
            'user_id'    => $row->user_id,
            'type'       => $row->type,
            'title'      => $row->title,
            'content'    => $row->content ?? '',
            'link'       => $row->link,
            'icon'       => $row->icon,
            'is_read'    => (bool) $row->is_read,
            'created_at' => $row->created_at?->toDateTimeString(),
            'meta'       => $row->meta ?? [],
        ];
    }

    /**
     * Seed 3 promo mặc định cho mỗi user — chỉ 1 lần nhờ unique(user_id, notif_key).
     */
    private static function seedPromos(int $userId): void
    {
        $promos = [
            [
                'key'      => 'PROMO-SEED-discount',
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
                'key'      => 'PROMO-SEED-freeship',
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
                'key'      => 'PROMO-SEED-starterkit',
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
            // INSERT IGNORE bằng firstOrCreate trên (user_id, notif_key) — unique constraint protects.
            Notification::firstOrCreate(
                ['user_id' => $userId, 'notif_key' => $p['key']],
                [
                    'type'       => 'promo',
                    'title'      => $p['title'],
                    'content'    => $p['content'],
                    'link'       => $p['link'],
                    'icon'       => $p['icon'],
                    'is_read'    => false,
                    'created_at' => now()->subMinutes(15 + $i * 10),
                    'meta'       => [
                        'cta'         => $p['cta'],
                        'banner'      => $p['banner'],
                        'code'        => $p['code'],
                        'valid_until' => $p['valid_until'],
                        'details'     => $p['details'],
                        'highlights'  => $p['highlights'],
                    ],
                ]
            );
        }
    }

    /**
     * Sinh notification cho mỗi stage timeline đã đạt trên mỗi đơn của user.
     * Orders vẫn ở session (per architecture doc) → đọc từ session.
     */
    private static function syncOrderTimelines(int $userId): void
    {
        $orders = session('orders', []);
        foreach ($orders as $order) {
            if (($order['user_id'] ?? null) !== $userId) continue;
            $orderId = $order['id'] ?? null;
            if (!$orderId) continue;

            $timeline = OrderTimeline::compute($order);
            foreach ($timeline['steps'] as $step) {
                if (!($step['is_done'] ?? false) && !($step['is_current'] ?? false)) continue;
                $stage = $step['key'];
                if (!isset(self::ORDER_STAGE_TEMPLATES[$stage])) continue;

                $tpl = self::ORDER_STAGE_TEMPLATES[$stage];
                Notification::firstOrCreate(
                    ['user_id' => $userId, 'notif_key' => "ORDER-{$orderId}-{$stage}"],
                    [
                        'type'       => 'order',
                        'title'      => str_replace(':id', $orderId, $tpl['title']),
                        'content'    => $tpl['body'],
                        'link'       => "/don-hang/{$orderId}",
                        'icon'       => $tpl['icon'],
                        'is_read'    => false,
                        'created_at' => $order['created_at'] ?? now(),
                        'meta'       => ['order_id' => $orderId, 'stage' => $stage],
                    ]
                );
            }
        }
    }

    /**
     * Pull broadcasts deliverable cho user này từ DB → notifications cá nhân.
     */
    private static function syncBroadcastQueue(int $userId): void
    {
        $user = Auth::user();
        if (!$user) return;

        $broadcasts = BroadcastQueue::deliverableFor($userId, $user->email ?? '', $user->role ?? 'user');
        foreach ($broadcasts as $b) {
            $notifKey = "BROADCAST-{$b['id']}";

            $created = Notification::firstOrCreate(
                ['user_id' => $userId, 'notif_key' => $notifKey],
                [
                    'type'       => $b['type']    ?? 'promo',
                    'title'      => $b['title']   ?? 'Thông báo',
                    'content'    => $b['content'] ?? '',
                    'link'       => $b['link']    ?? null,
                    'icon'       => $b['icon']    ?? 'info',
                    'is_read'    => false,
                    'created_at' => $b['send_at'] ?? now(),
                    'meta'       => $b['meta']    ?? [],
                ]
            );

            // Đánh dấu đã deliver — không push lại lần sau.
            if ($created->wasRecentlyCreated) {
                BroadcastQueue::markDelivered((string) $b['id'], $userId);
            }
        }
    }
}
