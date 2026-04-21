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
}
