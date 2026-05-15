<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Khi tài khoản đang đăng nhập bị admin chuyển sang trạng thái 'blocked',
 * middleware này phát hiện ngay ở request kế tiếp, đăng xuất, huỷ session
 * và chuyển về trang login kèm thông báo để user khiếu nại với shop.
 *
 * Chạy ở mọi request web (cả route công khai) sau khi session đã khởi động —
 * vì user có thể truy cập các trang public như /shop, / mà vẫn cần bị đẩy ra.
 */
class EnsureUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Refresh từ DB để biết status mới nhất (admin có thể vừa khoá)
            $fresh = $user->fresh();

            if ($fresh === null || ($fresh->status ?? 'active') !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Tài khoản của bạn đã bị khoá. Bạn đã được đăng xuất. '
                         . 'Vui lòng liên hệ shop để khiếu nại.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                        'reason'  => 'account_blocked',
                    ], 403);
                }

                return redirect()->route('login')
                    ->withErrors(['email' => $message])
                    ->with('account_blocked', $message);
            }
        }

        return $next($request);
    }
}
