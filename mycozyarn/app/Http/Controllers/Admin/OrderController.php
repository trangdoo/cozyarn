<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Support\AdminInbox;
use App\Support\OrderStore;
use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

/**
 * Admin orders — full management.
 * Session-based demo; khi migrate DB thay allOrders()/saveOrder() bằng Eloquent.
 */
class OrderController extends Controller
{
    private const PAGE_SIZE = 15;

    /* ═══════════════════════ list + stats ═══════════════════════ */

    public function index(Request $request)
    {
        // Admin đã vào trang đơn → coi như đã đọc các notification order_new/order_paid.
        AdminInbox::markTypeRead(['order_new', 'order_paid']);

        $all = $this->allOrders();
        usort($all, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $from   = $request->query('from', '');
        $to     = $request->query('to', '');

        $filtered = array_filter($all, function ($o) use ($q, $status, $from, $to) {
            if ($q !== '') {
                $hay = mb_strtolower(($o['id'] ?? '') . ' ' . ($o['name'] ?? '') . ' ' . ($o['phone'] ?? '') . ' ' . ($o['email'] ?? ''));
                if (!str_contains($hay, mb_strtolower($q))) return false;
            }
            if ($status !== 'all') {
                if ($this->stageOf($o) !== $status) return false;
            }
            if ($from && ($o['created_at'] ?? '') < $from) return false;
            if ($to   && ($o['created_at'] ?? '') > $to . ' 23:59:59') return false;
            return true;
        });

        $page  = max(1, (int) $request->query('page', 1));
        $items = \array_slice($filtered, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE);

        $paginator = new LengthAwarePaginator(
            $items,
            \count($filtered),
            self::PAGE_SIZE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.orders.index', [
            'orders' => $paginator,
            'filter' => compact('q', 'status', 'from', 'to'),
            'stats'  => $this->computeStats($all),
        ]);
    }

    /* ═══════════════════════ show + customer + history ═══════════════════════ */

    public function show(string $id)
    {
        AdminInbox::markTypeRead(['order_new', 'order_paid']);

        $order = OrderStore::find($id);
        abort_unless($order !== null, 404);

        $timeline = OrderTimeline::compute($order);

        $customer = null;
        if (!empty($order['user_id'])) {
            $customer = \App\Models\User::find($order['user_id']);
        }
        $otherOrders = array_values(array_filter($this->allOrders(), fn($o) =>
            ($o['user_id'] ?? null) === ($order['user_id'] ?? null) && ($o['id'] ?? '') !== $id
        ));

        // Reviews cho đơn này
        $allReviews = session('reviews', []);
        $orderReviews = [];
        foreach ($order['items'] ?? [] as $item) {
            $key = ($item['key'] ?? '');
            $rKey = $id . '::' . $key;
            if (isset($allReviews[$rKey])) {
                $orderReviews[$key] = $allReviews[$rKey] + ['item' => $item];
            }
        }

        return view('admin.orders.show', [
            'order'        => $order,
            'timeline'     => $timeline,
            'customer'     => $customer,
            'otherOrders'  => $otherOrders,
            'orderReviews' => $orderReviews,
            'history'      => $this->buildFullHistory($order),
        ]);
    }

    private function buildFullHistory(array $order): array
    {
        $history = [];

        // 1. Đặt đơn
        $history[] = [
            'from' => null,
            'to'   => 'pending',
            'by'   => $order['name'] ?? 'Khách hàng',
            'at'   => $order['created_at'] ?? now()->toDateTimeString(),
            'note' => 'Khách tạo đơn hàng' . (!empty($order['payment']) ? ' — thanh toán: ' . strtoupper($order['payment']) : ''),
        ];

        // 2. Thanh toán (nếu có)
        if (!empty($order['paid_at'])) {
            $history[] = [
                'from' => 'pending',
                'to'   => 'paid',
                'by'   => $order['name'] ?? 'Khách hàng',
                'at'   => $order['paid_at'],
                'note' => 'Đã thanh toán',
            ];
        }

        // 3. Các lần admin/user chuyển trạng thái
        foreach ($order['status_history'] ?? [] as $h) {
            $history[] = $h;
        }

        return $history;
    }

    /* ═══════════════════════ status actions ═══════════════════════ */

    public function confirm(string $id)
    {
        return $this->transition($id, 'confirmed', allowedFrom: ['pending', 'placed'], label: 'Đã xác nhận đơn.');
    }

    public function ship(string $id)
    {
        return $this->transition($id, 'shipping', allowedFrom: ['confirmed'], label: 'Đã bàn giao cho đơn vị vận chuyển.');
    }

    public function deliver(string $id)
    {
        return $this->transition($id, 'delivered', allowedFrom: ['shipping'], label: 'Đã xác nhận giao thành công.');
    }

    public function approveCancel(string $id)
    {
        $by = auth()->user()->name ?? 'Admin';
        $ok = OrderStore::transition($id, 'cancelled',
            ['pending', 'placed', 'confirmed'], $by, 'Admin duyệt huỷ đơn');
        return back()->with('cart_flash', $ok
            ? 'Đã duyệt huỷ đơn.'
            : 'Đơn đã giao cho vận chuyển, không thể duyệt huỷ.');
    }

    public function approveReturn(string $id)
    {
        $by = auth()->user()->name ?? 'Admin';
        $ok = OrderStore::transition($id, 'returned',
            ['return_requested'], $by, 'Admin duyệt hoàn tiền');
        return back()->with('cart_flash', $ok
            ? 'Đã duyệt hoàn tiền cho khách.'
            : 'Đơn không ở trạng thái yêu cầu trả hàng.');
    }

    public function rejectReturn(Request $request, string $id)
    {
        $reason = trim((string) $request->input('reason', ''));
        $by     = auth()->user()->name ?? 'Admin';
        $note   = 'Admin từ chối trả hàng' . ($reason !== '' ? ': ' . $reason : '');
        $ok = OrderStore::transition($id, 'delivered', ['return_requested'], $by, $note);

        if ($ok && $reason !== '') {
            Order::where('id', (int) $id)->update(['return_reason' => $reason]);
        }
        return back()->with('cart_flash', $ok
            ? 'Đã từ chối yêu cầu trả hàng.'
            : 'Đơn không ở trạng thái yêu cầu trả hàng.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,delivered,received,cancelled,return_requested,returned',
            'note'   => 'nullable|string|max:300',
        ]);

        $order = Order::find((int) $id);
        abort_unless($order, 404);

        $by   = auth()->user()->name ?? 'Admin';
        $from = (string) $order->status;
        OrderStore::pushHistory($id, $from, $data['status'], $by, $data['note'] ?? 'Admin cập nhật thủ công');
        $order->update(['status' => $data['status']]);

        return back()->with('cart_flash', 'Đã cập nhật trạng thái.');
    }

    /* ═══════════════════════ bulk + delete ═══════════════════════ */

    public function bulkConfirm(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'string']);
        $by = auth()->user()->name ?? 'Admin';
        $n  = 0;
        foreach ($data['ids'] as $id) {
            if (OrderStore::transition($id, 'confirmed', ['pending', 'placed'], $by, 'Bulk confirm')) {
                $n++;
            }
        }
        return back()->with('cart_flash', "Đã xác nhận {$n} đơn.");
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'string']);
        $intIds = array_map('intval', $data['ids']);
        $n = Order::whereIn('id', $intIds)->delete();
        return back()->with('cart_flash', "Đã xoá {$n} đơn.");
    }

    public function destroy(string $id)
    {
        Order::where('id', (int) $id)->delete();
        return redirect()->route('admin.orders.index')->with('cart_flash', 'Đã xoá đơn.');
    }

    /* ═══════════════════════ export ═══════════════════════ */

    public function export(Request $request, string $format = 'csv')
    {
        abort_unless(\in_array($format, ['csv', 'json'], true), 404);
        $all = $this->allOrders();

        $ids = $request->query('ids');
        if ($ids) {
            $keys = explode(',', $ids);
            $all = array_values(array_filter($all, fn($o) => \in_array($o['id'] ?? '', $keys, true)));
        }

        $rows = array_map(fn($o) => [
            'id'         => $o['id'] ?? '',
            'created_at' => $o['created_at'] ?? '',
            'customer'   => $o['name'] ?? '',
            'phone'      => $o['phone'] ?? '',
            'email'      => $o['email'] ?? '',
            'address'    => trim(($o['address'] ?? '') . ', ' . ($o['district'] ?? '') . ', ' . ($o['province'] ?? ''), ', '),
            'items_count'=> \count($o['items'] ?? []),
            'subtotal'   => (int) ($o['subtotal'] ?? 0),
            'shipping'   => (int) ($o['shippingFee'] ?? 0),
            'total'      => (int) ($o['total'] ?? 0),
            'payment'    => strtoupper($o['payment'] ?? ''),
            'status'     => $this->stageOf($o),
        ], $all);

        $filename = 'orders-' . now()->format('Ymd-His');

        if ($format === 'json') {
            return Response::make(
                json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                200,
                [
                    'Content-Type'        => 'application/json; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
                ]
            );
        }

        $cols = array_keys($rows[0] ?? [
            'id'=>'','created_at'=>'','customer'=>'','phone'=>'','email'=>'','address'=>'',
            'items_count'=>'','subtotal'=>'','shipping'=>'','total'=>'','payment'=>'','status'=>'',
        ]);

        return Response::streamDownload(function () use ($rows, $cols) {
            $out = fopen('php://output', 'w');
            fprintf($out, \chr(0xEF) . \chr(0xBB) . \chr(0xBF));
            fputcsv($out, $cols);
            foreach ($rows as $r) fputcsv($out, array_map(fn($c) => $r[$c] ?? '', $cols));
            fclose($out);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ═══════════════════════ review reply ═══════════════════════ */

    public function replyReview(Request $request, string $orderId, string $itemKey)
    {
        $data = $request->validate(['content' => 'required|string|max:1000']);

        $reviewKey = "{$orderId}::{$itemKey}";
        $reviews = session('reviews', []);
        abort_unless(isset($reviews[$reviewKey]), 404);

        $reviews[$reviewKey]['admin_reply'] = [
            'content'    => $data['content'],
            'by'         => auth()->user()->name ?? 'Admin',
            'created_at' => now()->toDateTimeString(),
        ];
        session(['reviews' => $reviews]);

        return back()->with('cart_flash', 'Đã phản hồi đánh giá.');
    }

    /* ═══════════════════════ helpers ═══════════════════════ */

    private function allOrders(): array
    {
        return OrderStore::all();
    }

    private function stageOf(array $order): string
    {
        $raw = $order['status'] ?? '';
        if (\in_array($raw, ['cancelled', 'returned', 'return_requested', 'received'], true)) return $raw;
        return OrderTimeline::currentKey($order);
    }

    private function transition(string $id, string $to, array $allowedFrom, string $label)
    {
        $by = auth()->user()->name ?? 'Admin';
        $ok = OrderStore::transition($id, $to, $allowedFrom, $by, $label);
        return back()->with('cart_flash', $ok
            ? $label
            : "Không thể chuyển sang '{$to}' (sai trạng thái nguồn).");
    }

    private function computeStats(array $orders): array
    {
        $now = now();
        $today     = $now->copy()->startOfDay()->toDateTimeString();
        $thisMonth = $now->copy()->startOfMonth()->toDateTimeString();

        $all        = \count($orders);
        $pending    = 0;
        $shipping   = 0;
        $delivered  = 0;
        $cancelled  = 0;
        $returnReq  = 0;
        $revenueToday = 0;
        $revenueMonth = 0;
        $revenueAll   = 0;

        foreach ($orders as $o) {
            $stage = $this->stageOf($o);
            if (\in_array($stage, ['pending', 'placed'], true)) $pending++;
            if ($stage === 'shipping')        $shipping++;
            if (\in_array($stage, ['delivered', 'received'], true)) $delivered++;
            if ($stage === 'cancelled')       $cancelled++;
            if ($stage === 'return_requested') $returnReq++;

            // Revenue — không tính đơn huỷ / đã trả
            if (!\in_array($stage, ['cancelled', 'returned'], true)) {
                $total = (int) ($o['total'] ?? 0);
                $revenueAll += $total;
                if (($o['created_at'] ?? '') >= $today)     $revenueToday += $total;
                if (($o['created_at'] ?? '') >= $thisMonth) $revenueMonth += $total;
            }
        }

        return compact('all', 'pending', 'shipping', 'delivered', 'cancelled', 'returnReq',
                       'revenueToday', 'revenueMonth', 'revenueAll');
    }
}
