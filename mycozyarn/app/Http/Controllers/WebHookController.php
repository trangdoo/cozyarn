<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Support\AdminInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebHookController
{
    public function sepay(Request $request): JsonResponse
    {
        // Bảo mật thật sự nằm ở Apikey — sai key thì từ chối thẳng (SePay sẽ retry).
        if (! $this->verifyApiKey($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Sau khi auth pass, contract của webhook là LUÔN trả 200 để SePay không retry
        // vô hạn. Payload không hợp lệ (ping test, missing field) chỉ log và no-op —
        // tránh trường hợp "Gửi thử" trên dashboard báo 400 Bad Request.
        $body = (string) $request->getContent();
        if ($body === '') {
            Log::info('SePay webhook: empty body (likely connectivity test from dashboard).');
            return response()->json(['success' => true, 'message' => 'pong']);
        }

        $data = json_decode($body, true);
        if (! is_array($data)) {
            Log::warning('SePay webhook: body is not valid JSON.', ['body' => mb_substr($body, 0, 500)]);
            return response()->json(['success' => true, 'message' => 'accepted (non-json)']);
        }

        if (empty($data['id'])) {
            Log::info('SePay webhook: payload missing "id" — likely a test ping.', ['payload' => $data]);
            return response()->json(['success' => true, 'message' => 'accepted (no transaction id)']);
        }

        try {
            $isNew = DB::transaction(function () use ($data, $body) {
                $existing = Transaction::where('sepay_id', $data['id'])->lockForUpdate()->first();
                if ($existing) {
                    return false;
                }

                Transaction::create([
                    'sepay_id'         => $data['id'],
                    'gateway'          => $data['gateway'] ?? '',
                    'transaction_date' => $data['transactionDate'] ?? now(),
                    'account_number'   => $data['accountNumber'] ?? null,
                    'sub_account'      => $data['subAccount'] ?? null,
                    'code'             => $data['code'] ?? null,
                    'amount_in'        => ($data['transferType'] ?? null) === 'in'  ? (int) ($data['transferAmount'] ?? 0) : 0,
                    'amount_out'       => ($data['transferType'] ?? null) === 'out' ? (int) ($data['transferAmount'] ?? 0) : 0,
                    'accumulated'      => (int) ($data['accumulated'] ?? 0),
                    'content'          => $data['content'] ?? null,
                    'reference_code'   => $data['referenceCode'] ?? null,
                    'body'             => $body,
                ]);

                return true;
            });

            if ($isNew) {
                $this->maybeMarkOrderPaid($data);
            }

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error('SePay webhook error', ['error' => $e->getMessage(), 'sepay_id' => $data['id'] ?? null]);
            // 500 vẫn báo lỗi thật sự (DB down, etc.) — SePay nên retry.
            return response()->json(['success' => false, 'message' => 'Internal error'], 500);
        }
    }

    /**
     * SePay gửi header `Authorization: Apikey <key>` (xem docs.sepay.vn/lap-trinh-webhooks).
     * Verify bằng hash_equals để tránh timing attack.
     */
    private function verifyApiKey(Request $request): bool
    {
        $expected = (string) config('services.sepay.api_key');
        if ($expected === '') {
            Log::warning('SePay API key is not configured');
            return false;
        }

        $header = (string) $request->header('Authorization', '');
        if (! preg_match('/^Apikey\s+(.+)$/i', $header, $m)) {
            return false;
        }

        return hash_equals($expected, trim($m[1]));
    }

    /**
     * Chỉ chạy khi giao dịch lần đầu được lưu. Match memo chuyển khoản (prefix "DH" +
     * Order.id, xem CheckoutController::PAYMENT_MEMO_PREFIX) với Order.id và đánh dấu
     * payment_status = 'paid' nếu số tiền >= total_amount.
     */
    private function maybeMarkOrderPaid(array $data): void
    {
        if (($data['transferType'] ?? null) !== 'in') {
            return;
        }

        $orderId = $this->extractOrderId($data);
        if ($orderId === null) {
            return;
        }

        $amount = (int) ($data['transferAmount'] ?? 0);

        $affected = Order::where('id', $orderId)
            ->where('payment_status', 'pending')
            ->where('total_amount', '<=', $amount)
            ->update([
                'payment_status' => 'paid',
                'updated_at'     => now(),
            ]);

        // Chỉ push admin inbox khi update thực sự flip pending → paid (affected > 0).
        // Tránh trùng lặp khi webhook retry cùng giao dịch.
        if ($affected > 0) {
            $order = Order::find($orderId);
            AdminInbox::push([
                'type'    => 'order_paid',
                'title'   => "Đơn #DH{$orderId} đã thanh toán",
                'content' => sprintf(
                    'Đã nhận %s ₫ qua chuyển khoản — chờ admin xác nhận đơn.',
                    number_format($amount, 0, ',', '.'),
                ),
                'link'    => route('admin.orders.show', ['id' => $orderId]),
                'meta'    => [
                    'order_id' => $orderId,
                    'payment'  => 'bank',
                    'amount'   => $amount,
                    'total'    => $order->total_amount ?? null,
                ],
            ]);
        }
    }

    /**
     * Memo shop dùng format "DH<id>". SePay có thể đã strip prefix qua regex match
     * (code = "<id>"), hoặc trả về full memo (code = "DH<id>"), hoặc memo chỉ nằm
     * trong content. Thử cả 3 nguồn để chịu được mọi cấu hình SePay.
     */
    private function extractOrderId(array $data): ?int
    {
        foreach (['code', 'content'] as $field) {
            $val = (string) ($data[$field] ?? '');
            if ($val !== '' && preg_match('/DH(\d+)/i', $val, $m)) {
                return (int) $m[1];
            }
        }

        $code = trim((string) ($data['code'] ?? ''));
        if ($code !== '' && ctype_digit($code)) {
            return (int) $code;
        }

        return null;
    }
}
