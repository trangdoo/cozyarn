<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('user.auth.login');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('user.auth.register');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember    = (bool) $request->input('remember', false);

        // Báo lỗi rõ ràng hơn khi tài khoản bị khoá
        $user = User::where('email', $credentials['email'])->first();
        if ($user && ($user->status ?? 'active') === 'blocked') {
            return back()
                ->withErrors(['email' => 'Tài khoản đã bị khoá. Vui lòng liên hệ shop để biết thêm chi tiết.'])
                ->withInput($request->only('email'));
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Admin vào dashboard, user về trang chủ
            if (Auth::user()->isAdmin()) {
                return redirect()->intended('/admin');
            }
            return redirect()->intended('/');
        }

        return back()
            ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])
            ->withInput($request->only('email'));
    }

    public function register(RegisterRequest $request)
    {
        // Password được auto-hash qua cast 'password' => 'hashed' trong User model
        $user = User::create([
            'name'     => trim((string) $request->input('name')),
            'email'    => strtolower(trim((string) $request->input('email'))),
            'password' => (string) $request->input('password'),
            'phone'    => $request->input('phone'),
            'role'     => 'user',
            'status'   => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('cart_flash', "Chào mừng {$user->name} đến với CozyYarn!");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('cart_flash', 'Bạn đã đăng xuất.');
    }

    /* ═══════════════════════ FORGOT PASSWORD ═══════════════════════ */

    public function showForgotForm()
    {
        if (Auth::check()) return redirect('/');
        return view('user.auth.forgot');
    }

    public function verifyForgotEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không hợp lệ.',
        ]);

        $email = strtolower(trim($data['email']));
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => 'Không tìm thấy tài khoản với email này.'])
                ->withInput();
        }

        if (($user->status ?? 'active') === 'blocked') {
            return back()
                ->withErrors(['email' => 'Tài khoản đã bị khoá. Vui lòng liên hệ shop.'])
                ->withInput();
        }

        // Lưu email đã xác thực vào session, cho phép sang bước đặt lại mật khẩu
        $request->session()->put('password_reset_email', $email);
        $request->session()->put('password_reset_expires_at', now()->addMinutes(15)->toDateTimeString());

        return redirect()->route('password.reset');
    }

    public function showResetForm(Request $request)
    {
        if (Auth::check()) return redirect('/');

        $email   = $request->session()->get('password_reset_email');
        $expires = $request->session()->get('password_reset_expires_at');

        if (!$email || !$expires || now()->gt($expires)) {
            $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thử lại.']);
        }

        return view('user.auth.reset', ['email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $email   = $request->session()->get('password_reset_email');
        $expires = $request->session()->get('password_reset_expires_at');

        if (!$email || !$expires || now()->gt($expires)) {
            $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thử lại.']);
        }

        $data = $request->validate([
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Không tìm thấy tài khoản.']);
        }

        $user->password = $data['password'];
        $user->save();

        $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);

        return redirect()->route('login')
            ->with('cart_flash', 'Đổi mật khẩu thành công. Mời bạn đăng nhập.');
    }
}
