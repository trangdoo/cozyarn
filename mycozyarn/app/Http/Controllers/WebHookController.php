<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebHookController
{
    private const int REPLAY_WINDOW_SECONDS = 300;

    public function sepay(Request $request): JsonResponse
    {
        $body = $request->getContent();
        if ($body === '' || $body === false) {
            return response()->json(['success' => false, 'message' => 'Empty body'], 400);
        }

        if (! $this->verifySignature($request, $body)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $data = json_decode($body, true);
        if (! is_array($data) || empty($data['id'])) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
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
            return response()->json(['success' => false, 'message' => 'Internal error'], 500);
        }
    }

    private function verifySignature(Request $request, string $body): bool
    {
        $secret = config('services.sepay.webhook_secret');
        if (empty($secret)) {
            Log::warning('SePay webhook secret is not configured');
            return false;
        }

        $signature = (string) $request->header('X-Sepay-Signature', '');
        $timestamp = (int) $request->header('X-Sepay-Timestamp', 0);

        if (abs(time() - $timestamp) > self::REPLAY_WINDOW_SECONDS) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Chỉ chạy khi giao dịch lần đầu được lưu. Match `code` (memo chuyển khoản) với
     * Order.id và đánh dấu payment_status = 'paid' nếu số tiền >= total_amount.
     */
    private function maybeMarkOrderPaid(array $data): void
    {
        if (($data['transferType'] ?? null) !== 'in') {
            return;
        }

        $code = trim((string) ($data['code'] ?? ''));
        if ($code === '' || ! ctype_digit($code)) {
            return;
        }

        $amount = (int) ($data['transferAmount'] ?? 0);

        Order::where('id', (int) $code)
            ->where('payment_status', 'pending')
            ->where('total_amount', '<=', $amount)
            ->update([
                'payment_status' => 'paid',
                'updated_at'     => now(),
            ]);
    }
}
