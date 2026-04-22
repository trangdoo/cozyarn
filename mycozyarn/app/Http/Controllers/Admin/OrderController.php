<?php

namespace App\Http\Controllers\Admin;

use App\Support\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $orders = session('orders', []);
        $order  = $orders[$id] ?? null;
        abort_unless($order !== null, 404);

        $timeline = OrderTimeline::compute($order);

        // Tìm customer info: từ user_id + đơn hàng khác
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
            'history'      => $order['status_history'] ?? [],
        ]);
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
        $orders = session('orders', []);
        abort_unless(isset($orders[$id]), 404);

        $stage = $this->stageOf($orders[$id]);
        if (!\in_array($stage, ['pending', 'placed', 'confirmed'], true)) {
            return back()->with('cart_flash', 'Đơn đã giao cho vận chuyển, không thể duyệt huỷ.');
        }

        $orders[$id] = $this->pushHistory($orders[$id], 'cancelled', 'Admin duyệt huỷ đơn');
        $orders[$id]['status']       = 'cancelled';
        $orders[$id]['cancelled_at'] = now()->toDateTimeString();
        session(['orders' => $orders]);

        return back()->with('cart_flash', 'Đã duyệt huỷ đơn.');
    }

    public function approveReturn(string $id)
    {
        $orders = session('orders', []);
        abort_unless(isset($orders[$id]), 404);
        if (($orders[$id]['status'] ?? '') !== 'return_requested') {
            return back()->with('cart_flash', 'Đơn không ở trạng thái yêu cầu trả hàng.');
        }

        $orders[$id] = $this->pushHistory($orders[$id], 'returned', 'Admin duyệt hoàn tiền');
        $orders[$id]['status']             = 'returned';
        $orders[$id]['refunded_at']        = now()->toDateTimeString();
        session(['orders' => $orders]);

        return back()->with('cart_flash', 'Đã duyệt hoàn tiền cho khách.');
    }

    public function rejectReturn(Request $request, string $id)
    {
        $reason = trim((string) $request->input('reason', ''));
        $orders = session('orders', []);
        abort_unless(isset($orders[$id]), 404);
        if (($orders[$id]['status'] ?? '') !== 'return_requested') {
            return back()->with('cart_flash', 'Đơn không ở trạng thái yêu cầu trả hàng.');
        }

        $orders[$id] = $this->pushHistory($orders[$id], 'delivered',
            'Admin từ chối trả hàng' . ($reason !== '' ? ': ' . $reason : ''));
        $orders[$id]['status']           = 'delivered';
        $orders[$id]['return_rejected_at'] = now()->toDateTimeString();
        $orders[$id]['return_rejection']  = $reason ?: null;
        session(['orders' => $orders]);

        return back()->with('cart_flash', 'Đã từ chối yêu cầu trả hàng.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,delivered,received,cancelled,return_requested,returned',
            'note'   => 'nullable|string|max:300',
        ]);

        $orders = session('orders', []);
        abort_unless(isset($orders[$id]), 404);

        $orders[$id] = $this->pushHistory($orders[$id], $data['status'], $data['note'] ?? 'Admin cập nhật thủ công');
        $orders[$id]['status'] = $data['status'];
        session(['orders' => $orders]);

        return back()->with('cart_flash', 'Đã cập nhật trạng thái.');
    }

    /* ═══════════════════════ bulk + delete ═══════════════════════ */

    public function bulkConfirm(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'string']);
        $orders = session('orders', []);
        $n = 0;
        foreach ($data['ids'] as $id) {
            if (!isset($orders[$id])) continue;
            $stage = $this->stageOf($orders[$id]);
            if (!\in_array($stage, ['pending', 'placed'], true)) continue;
            $orders[$id] = $this->pushHistory($orders[$id], 'confirmed', 'Bulk confirm');
            $orders[$id]['status'] = 'confirmed';
            $n++;
        }
        session(['orders' => $orders]);
        return back()->with('cart_flash', "Đã xác nhận {$n} đơn.");
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'string']);
        $orders = session('orders', []);
        $n = 0;
        foreach ($data['ids'] as $id) {
            if (isset($orders[$id])) {
                unset($orders[$id]);
                $n++;
            }
        }
        session(['orders' => $orders]);
        return back()->with('cart_flash', "Đã xoá {$n} đơn.");
    }

    public function destroy(string $id)
    {
        $orders = session('orders', []);
        unset($orders[$id]);
        session(['orders' => $orders]);
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
        return array_values(session('orders', []));
    }

    private function stageOf(array $order): string
    {
        $raw = $order['status'] ?? '';
        if (\in_array($raw, ['cancelled', 'returned', 'return_requested', 'received'], true)) return $raw;
        return OrderTimeline::currentKey($order);
    }

    private function transition(string $id, string $to, array $allowedFrom, string $label)
    {
        $orders = session('orders', []);
        abort_unless(isset($orders[$id]), 404);

        $stage = $this->stageOf($orders[$id]);
        if (!\in_array($stage, $allowedFrom, true)) {
            return back()->with('cart_flash', "Không thể chuyển từ '{$stage}' sang '{$to}'.");
        }

        $orders[$id] = $this->pushHistory($orders[$id], $to, $label);
        $orders[$id]['status'] = $to;
        session(['orders' => $orders]);
        return back()->with('cart_flash', $label);
    }

    private function pushHistory(array $order, string $toStatus, string $note): array
    {
        $history = $order['status_history'] ?? [];
        $history[] = [
            'from' => $this->stageOf($order),
            'to'   => $toStatus,
            'by'   => auth()->user()->name ?? 'Admin',
            'at'   => now()->toDateTimeString(),
            'note' => $note,
        ];
        $order['status_history'] = $history;
        return $order;
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
