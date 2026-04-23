<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Support\OrderTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 15;

    public function index(Request $request)
    {
        $shop   = require resource_path('shop.php');
        $orders = array_values(session('orders', []));

        // ═══════════ 1. Day picker ═══════════
        $dayInput = $request->query('day');
        try {
            $selectedDay = $dayInput ? Carbon::parse($dayInput) : now();
            if ($selectedDay->gt(now())) $selectedDay = now();
        } catch (\Throwable) {
            $selectedDay = now();
        }
        $prevDay = $selectedDay->copy()->subDay();
        $isToday = $selectedDay->isSameDay(now());

        // ═══════════ 2. KPI cho ngày được chọn + so với ngày trước ═══════════
        $dayBuckets = [
            'current' => ['revenue' => 0, 'orders' => 0, 'items' => 0, 'pending' => 0, 'delivered' => 0, 'cancelled' => 0],
            'prev'    => ['revenue' => 0, 'orders' => 0, 'items' => 0],
        ];
        $todayStart = $selectedDay->copy()->startOfDay()->toDateTimeString();
        $todayEnd   = $selectedDay->copy()->endOfDay()->toDateTimeString();
        $prevStart  = $prevDay->copy()->startOfDay()->toDateTimeString();
        $prevEnd    = $prevDay->copy()->endOfDay()->toDateTimeString();

        foreach ($orders as $o) {
            $ts    = $o['created_at'] ?? '';
            $stage = $this->stageOf($o);
            $total = (int) ($o['total'] ?? 0);
            $qty   = array_sum(array_map(fn($it) => (int) ($it['qty'] ?? 0), $o['items'] ?? []));

            if ($ts >= $todayStart && $ts <= $todayEnd) {
                $dayBuckets['current']['orders']++;
                $dayBuckets['current']['items'] += $qty;
                if (!\in_array($stage, ['cancelled', 'returned'], true)) $dayBuckets['current']['revenue'] += $total;
                if (\in_array($stage, ['pending', 'placed'], true))      $dayBuckets['current']['pending']++;
                if (\in_array($stage, ['delivered', 'received'], true))  $dayBuckets['current']['delivered']++;
                if ($stage === 'cancelled')                              $dayBuckets['current']['cancelled']++;
            } elseif ($ts >= $prevStart && $ts <= $prevEnd) {
                $dayBuckets['prev']['orders']++;
                $dayBuckets['prev']['items'] += $qty;
                if (!\in_array($stage, ['cancelled', 'returned'], true)) $dayBuckets['prev']['revenue'] += $total;
            }
        }

        $todayUsers = User::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $prevUsers  = User::whereBetween('created_at', [$prevStart,  $prevEnd])->count();

        $trend = fn($cur, $prev) => $prev > 0 ? round(($cur - $prev) / $prev * 100) : ($cur > 0 ? 100 : null);
        $aov = $dayBuckets['current']['orders'] > 0
            ? round($dayBuckets['current']['revenue'] / $dayBuckets['current']['orders'])
            : 0;
        $prevAov = $dayBuckets['prev']['orders'] > 0
            ? round($dayBuckets['prev']['revenue'] / $dayBuckets['prev']['orders'])
            : 0;

        // Sparkline 7 ngày kết thúc tại ngày được chọn
        $spark7 = $this->buildSparkline($orders, 7, $selectedDay);

        // ═══════════ 3. Action queue — cần xử lý ═══════════
        $actionQueue = [
            'pending'     => 0,   // đơn mới chờ xác nhận
            'shipping'    => 0,   // đơn đã đóng gói chờ chuyển
            'return_req'  => 0,   // yêu cầu trả hàng chờ duyệt
            'review_noreply' => 0, // đánh giá chưa phản hồi
            'low_stock'   => 0,   // sản phẩm sắp hết hàng
        ];
        foreach ($orders as $o) {
            $s = $this->stageOf($o);
            if (\in_array($s, ['pending', 'placed'], true)) $actionQueue['pending']++;
            if ($s === 'confirmed')                         $actionQueue['shipping']++;
            if ($s === 'return_requested')                  $actionQueue['return_req']++;
        }
        foreach (session('reviews', []) as $r) {
            if (empty($r['admin_reply'])) $actionQueue['review_noreply']++;
        }

        // Low stock từ shop.php + overlay admin edit (nếu có field quantity)
        $lowStockItems = [];
        foreach ($shop['products'] as $catSlug => $list) {
            foreach ($list as $p) {
                $stock = array_sum(array_column($p['variants'] ?? [], 'stock'));
                if ($stock === 0) $stock = (int) ($p['quantity'] ?? 0);
                if ($stock < self::LOW_STOCK_THRESHOLD) {
                    $lowStockItems[] = [
                        'name' => $p['name'],
                        'slug' => $p['slug'],
                        'category' => $catSlug,
                        'image' => $p['image'] ?? '/images/1.jpg',
                        'stock' => $stock,
                    ];
                }
            }
        }
        usort($lowStockItems, fn($a, $b) => $a['stock'] <=> $b['stock']);
        $actionQueue['low_stock'] = \count($lowStockItems);
        $lowStockItems = \array_slice($lowStockItems, 0, 6);

        // ═══════════ 4. Chart range ═══════════
        $range = $request->query('range', '7d');
        $endInput = $request->query('end');
        try {
            $endDate = $endInput ? Carbon::parse($endInput) : now();
            if ($endDate->gt(now())) $endDate = now();
        } catch (\Throwable) {
            $endDate = now();
        }
        $endDateStr = $endDate->toDateString();

        [$buckets, $rangeLabel] = $this->buildBuckets($range, $endDate);
        $this->fillBuckets($buckets, $orders);
        $prev = $this->buildPrevBuckets($range, $endDate);
        $this->fillBuckets($prev, $orders);

        // ═══════════ 5. Sales by category ═══════════
        $categorySales = [];
        foreach ($shop['categories'] as $slug => $cat) {
            $categorySales[$slug] = ['name' => $cat['name'], 'revenue' => 0, 'qty' => 0];
        }
        foreach ($orders as $o) {
            if (\in_array($this->stageOf($o), ['cancelled', 'returned'], true)) continue;
            foreach ($o['items'] ?? [] as $it) {
                $cat = $it['category'] ?? null;
                if (!$cat || !isset($categorySales[$cat])) continue;
                $categorySales[$cat]['qty']     += (int) ($it['qty'] ?? 0);
                $categorySales[$cat]['revenue'] += (int) (($it['price'] ?? 0) * ($it['qty'] ?? 0));
            }
        }
        uasort($categorySales, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $maxCatRevenue = max(array_column($categorySales, 'revenue') ?: [0]);

        // ═══════════ 6. Sales by payment method ═══════════
        $paymentStats = [
            'cod'  => ['label' => 'COD',          'count' => 0, 'revenue' => 0],
            'bank' => ['label' => 'Chuyển khoản', 'count' => 0, 'revenue' => 0],
            'momo' => ['label' => 'MoMo',         'count' => 0, 'revenue' => 0],
        ];
        $totalPaidOrders = 0;
        foreach ($orders as $o) {
            if (\in_array($this->stageOf($o), ['cancelled', 'returned'], true)) continue;
            $pm = $o['payment'] ?? 'cod';
            if (!isset($paymentStats[$pm])) continue;
            $paymentStats[$pm]['count']++;
            $paymentStats[$pm]['revenue'] += (int) ($o['total'] ?? 0);
            $totalPaidOrders++;
        }

        // ═══════════ 7. Top products (luỹ kế) ═══════════
        $productStats = [];
        foreach ($orders as $o) {
            if (\in_array($this->stageOf($o), ['cancelled', 'returned'], true)) continue;
            foreach ($o['items'] ?? [] as $it) {
                $key = $it['slug'] ?? $it['name'] ?? '';
                if (!$key) continue;
                if (!isset($productStats[$key])) {
                    $productStats[$key] = [
                        'name' => $it['name'] ?? '', 'image' => $it['image'] ?? '/images/1.jpg',
                        'qty' => 0, 'revenue' => 0,
                    ];
                }
                $productStats[$key]['qty']     += (int) ($it['qty'] ?? 0);
                $productStats[$key]['revenue'] += (int) (($it['price'] ?? 0) * ($it['qty'] ?? 0));
            }
        }
        usort($productStats, fn($a, $b) => $b['qty'] <=> $a['qty']);
        $topProducts = \array_slice($productStats, 0, 5);

        // ═══════════ 8. Status distribution (donut) ═══════════
        $statusDist = [
            'pending' => 0, 'confirmed' => 0, 'shipping' => 0,
            'delivered' => 0, 'received' => 0,
            'cancelled' => 0, 'return_req' => 0, 'returned' => 0,
        ];
        foreach ($orders as $o) {
            $s = $this->stageOf($o);
            if ($s === 'return_requested') $statusDist['return_req']++;
            elseif ($s === 'placed') $statusDist['pending']++;
            elseif (isset($statusDist[$s])) $statusDist[$s]++;
        }

        // ═══════════ 9. Reviews summary ═══════════
        $reviews = array_values(session('reviews', []));
        $reviewStats = [
            'total' => \count($reviews),
            'avg'   => 0,
            'noreply' => 0,
            'breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ];
        $sumRating = 0;
        foreach ($reviews as $r) {
            $rating = (int) ($r['rating'] ?? 5);
            if ($rating < 1 || $rating > 5) continue;
            $reviewStats['breakdown'][$rating]++;
            $sumRating += $rating;
            if (empty($r['admin_reply'])) $reviewStats['noreply']++;
        }
        if ($reviewStats['total'] > 0) {
            $reviewStats['avg'] = round($sumRating / $reviewStats['total'], 1);
        }
        $recentReviews = $reviews;
        usort($recentReviews, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentReviews = \array_slice($recentReviews, 0, 3);

        // ═══════════ 10. Customer insights ═══════════
        $customerOrderCounts = [];
        $customerSpending    = [];
        foreach ($orders as $o) {
            $uid = $o['user_id'] ?? null;
            if (!$uid) continue;
            if (\in_array($this->stageOf($o), ['cancelled', 'returned'], true)) continue;
            $customerOrderCounts[$uid] = ($customerOrderCounts[$uid] ?? 0) + 1;
            $customerSpending[$uid]    = ($customerSpending[$uid] ?? 0) + (int) ($o['total'] ?? 0);
        }
        $returningCount = \count(array_filter($customerOrderCounts, fn($c) => $c >= 2));

        // VIP: top 3 spenders
        arsort($customerSpending);
        $topSpenderIds = \array_slice(array_keys($customerSpending), 0, 3, true);
        $vipCustomers = [];
        if (!empty($topSpenderIds)) {
            $vipUsers = User::whereIn('id', $topSpenderIds)->get()->keyBy('id');
            foreach ($topSpenderIds as $uid) {
                if (!isset($vipUsers[$uid])) continue;
                $vipCustomers[] = [
                    'user'     => $vipUsers[$uid],
                    'spent'    => $customerSpending[$uid],
                    'orders'   => $customerOrderCounts[$uid] ?? 0,
                ];
            }
        }

        $customerInsights = [
            'total'     => User::count(),
            'new_today' => $todayUsers,
            'returning' => $returningCount,
            'vip'       => $vipCustomers,
        ];

        // ═══════════ 11. Recent notifications ═══════════
        $adminNotifs = array_values(session('notifications', []));
        usort($adminNotifs, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentNotifications = \array_slice($adminNotifs, 0, 5);

        // ═══════════ 13. Recent chat threads ═══════════
        $chatThreads = array_values(session('chats', []));
        foreach ($chatThreads as &$t) {
            $lastRead = $t['last_read_by_shop'] ?? '1970-01-01 00:00:00';
            $unread = 0;
            foreach ($t['messages'] ?? [] as $m) {
                if (($m['sender'] ?? '') === 'user' && ($m['created_at'] ?? '') > $lastRead) $unread++;
            }
            $t['unread'] = $unread;
        }
        unset($t);
        usort($chatThreads, function ($a, $b) {
            // Unread threads first, then by updated_at
            if (($a['unread'] ?? 0) > 0 xor ($b['unread'] ?? 0) > 0) {
                return ($b['unread'] ?? 0) <=> ($a['unread'] ?? 0);
            }
            return strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? '');
        });
        $recentChats = \array_slice($chatThreads, 0, 5);

        // ═══════════ 14. Recent reviews (with user name, order link) ═══════════
        $allReviewsList = [];
        foreach (session('reviews', []) as $key => $r) {
            $parts = explode('::', $key, 2);
            $r['_order_id'] = $parts[0] ?? '';
            $r['_item_key'] = $parts[1] ?? '';
            // Find the user from the related order
            $r['_user_name'] = '—';
            $r['_product_name'] = '';
            $r['_product_image'] = '/images/1.jpg';
            if ($r['_order_id'] && isset(session('orders', [])[$r['_order_id']])) {
                $relatedOrder = session('orders', [])[$r['_order_id']];
                $r['_user_name'] = $relatedOrder['name'] ?? '—';
                foreach ($relatedOrder['items'] ?? [] as $it) {
                    if (($it['key'] ?? '') === $r['_item_key']) {
                        $r['_product_name']  = $it['name'] ?? '';
                        $r['_product_image'] = $it['image'] ?? '/images/1.jpg';
                        break;
                    }
                }
            }
            $allReviewsList[] = $r;
        }
        usort($allReviewsList, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentReviewsList = \array_slice($allReviewsList, 0, 3);

        // ═══════════ 15. Recent orders ═══════════
        $recentOrders = $orders;
        usort($recentOrders, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentOrders = \array_slice($recentOrders, 0, 6);

        return view('admin.dashboard', [
            'range'       => $range,
            'rangeLabel'  => $rangeLabel,
            'endDate'     => $endDateStr,
            'selectedDay' => $selectedDay->toDateString(),
            'isToday'     => $isToday,
            'today' => [
                'date'              => $selectedDay->translatedFormat('l, d/m/Y'),
                'shortDate'         => $selectedDay->format('d/m/Y'),
                'revenue'           => $dayBuckets['current']['revenue'],
                'orders'            => $dayBuckets['current']['orders'],
                'items'             => $dayBuckets['current']['items'],
                'users'             => $todayUsers,
                'pending'           => $dayBuckets['current']['pending'],
                'delivered'         => $dayBuckets['current']['delivered'],
                'cancelled'         => $dayBuckets['current']['cancelled'],
                'aov'               => $aov,
                // Previous day values cho compare line
                'revenueYesterday'  => $dayBuckets['prev']['revenue'],
                'ordersYesterday'   => $dayBuckets['prev']['orders'],
                'itemsYesterday'    => $dayBuckets['prev']['items'],
                'usersYesterday'    => $prevUsers,
                'aovYesterday'      => $prevAov,
                // Trends
                'revenueTrend'      => $trend($dayBuckets['current']['revenue'], $dayBuckets['prev']['revenue']),
                'ordersTrend'       => $trend($dayBuckets['current']['orders'],  $dayBuckets['prev']['orders']),
                'itemsTrend'        => $trend($dayBuckets['current']['items'],   $dayBuckets['prev']['items']),
                'usersTrend'        => $trend($todayUsers,       $prevUsers),
                'aovTrend'          => $trend($aov,              $prevAov),
                // Sparklines
                'revenueSpark'      => $spark7['revenue'],
                'ordersSpark'       => $spark7['orders'],
                'usersSpark'        => $spark7['users'],
            ],
            'chart' => [
                'labels'      => array_column($buckets, 'label'),
                'revenue'     => array_column($buckets, 'revenue'),
                'orders'      => array_column($buckets, 'orders'),
                'prevRevenue' => array_column($prev,    'revenue'),
                'prevOrders'  => array_column($prev,    'orders'),
            ],
            'actionQueue'    => $actionQueue,
            'categorySales'  => $categorySales,
            'maxCatRevenue'  => $maxCatRevenue,
            'paymentStats'   => $paymentStats,
            'totalPaidOrders' => $totalPaidOrders,
            'topProducts'    => $topProducts,
            'statusDist'       => $statusDist,
            'lowStockItems'    => $lowStockItems,
            'recentOrders'     => $recentOrders,
            'reviewStats'         => $reviewStats,
            'recentReviews'       => $recentReviews,
            'recentReviewsList'   => $recentReviewsList,
            'recentNotifications' => $recentNotifications,
            'recentChats'         => $recentChats,
            'customerInsights'    => $customerInsights,
        ]);
    }

    /* ═══════════════════════ helpers ═══════════════════════ */

    private function stageOf(array $order): string
    {
        $raw = $order['status'] ?? '';
        if (\in_array($raw, ['cancelled', 'returned', 'return_requested', 'received'], true)) return $raw;
        return OrderTimeline::currentKey($order);
    }

    private function buildSparkline(array $orders, int $days, ?Carbon $refDay = null): array
    {
        $today = ($refDay ?? now())->copy()->startOfDay();
        $revenue   = array_fill(0, $days, 0);
        $ordersArr = array_fill(0, $days, 0);
        $users     = array_fill(0, $days, 0);

        foreach ($orders as $o) {
            $ts = $o['created_at'] ?? '';
            if (!$ts) continue;
            $d = Carbon::parse($ts)->startOfDay();
            $diff = $today->diffInDays($d, false);
            $idx = $days - 1 + (int) $diff;
            if ($idx < 0 || $idx >= $days) continue;
            if (!\in_array($this->stageOf($o), ['cancelled', 'returned'], true)) {
                $revenue[$idx] += (int) ($o['total'] ?? 0);
            }
            $ordersArr[$idx]++;
        }
        $userCounts = User::whereBetween('created_at', [
            $today->copy()->subDays($days - 1)->toDateTimeString(),
            $today->copy()->endOfDay()->toDateTimeString(),
        ])->get(['created_at']);
        foreach ($userCounts as $u) {
            $d = $u->created_at->startOfDay();
            $diff = $today->diffInDays($d, false);
            $idx = $days - 1 + (int) $diff;
            if ($idx >= 0 && $idx < $days) $users[$idx]++;
        }
        return ['revenue' => $revenue, 'orders' => $ordersArr, 'users' => $users];
    }

    private function buildBuckets(string $range, ?Carbon $endDate = null): array
    {
        $today = ($endDate ?? now())->copy()->startOfDay();
        $buckets = [];
        [$count, $unit, $label] = match ($range) {
            '30d' => [30, 'day',   '30 ngày'],
            '12w' => [12, 'week',  '12 tuần'],
            '12m' => [12, 'month', '12 tháng'],
            '5y'  => [5,  'year',  '5 năm'],
            default => [7, 'day',  '7 ngày'],
        };
        for ($i = $count - 1; $i >= 0; $i--) {
            switch ($unit) {
                case 'week':
                    $start = $today->copy()->subWeeks($i)->startOfWeek();
                    $key = $start->toDateString();
                    $lbl = "T{$start->weekOfYear}";
                    break;
                case 'month':
                    $start = $today->copy()->subMonthsNoOverflow($i)->startOfMonth();
                    $key = $start->format('Y-m');
                    $lbl = $start->format('m/Y');
                    break;
                case 'year':
                    $start = $today->copy()->subYearsNoOverflow($i)->startOfYear();
                    $key = $start->format('Y');
                    $lbl = $start->format('Y');
                    break;
                default:
                    $start = $today->copy()->subDays($i);
                    $key = $start->toDateString();
                    $lbl = $range === '30d' ? $start->format('d/m') : $start->translatedFormat('D');
            }
            $buckets[$key] = ['label' => $lbl, 'orders' => 0, 'revenue' => 0, '_unit' => $unit];
        }
        return [$buckets, $label];
    }

    private function buildPrevBuckets(string $range, ?Carbon $endDate = null): array
    {
        [$count, $unit] = match ($range) {
            '30d' => [30, 'day'], '12w' => [12, 'week'],
            '12m' => [12, 'month'], '5y' => [5, 'year'],
            default => [7, 'day'],
        };
        $today = ($endDate ?? now())->copy()->startOfDay();
        $buckets = [];
        for ($i = $count * 2 - 1; $i >= $count; $i--) {
            switch ($unit) {
                case 'week':  $key = $today->copy()->subWeeks($i)->startOfWeek()->toDateString(); break;
                case 'month': $key = $today->copy()->subMonthsNoOverflow($i)->format('Y-m'); break;
                case 'year':  $key = $today->copy()->subYearsNoOverflow($i)->format('Y'); break;
                default:      $key = $today->copy()->subDays($i)->toDateString();
            }
            $buckets[$key] = ['orders' => 0, 'revenue' => 0, '_unit' => $unit];
        }
        return $buckets;
    }

    private function fillBuckets(array &$buckets, array $orders): void
    {
        foreach ($orders as $o) {
            $ts = $o['created_at'] ?? '';
            if (!$ts) continue;
            if (\in_array($this->stageOf($o), ['cancelled', 'returned'], true)) continue;
            $d = Carbon::parse($ts);
            $unit = reset($buckets)['_unit'] ?? 'day';
            $key = match ($unit) {
                'week'  => $d->copy()->startOfWeek()->toDateString(),
                'month' => $d->format('Y-m'),
                'year'  => $d->format('Y'),
                default => $d->toDateString(),
            };
            if (!isset($buckets[$key])) continue;
            $buckets[$key]['orders']++;
            $buckets[$key]['revenue'] += (int) ($o['total'] ?? 0);
        }
    }
}
