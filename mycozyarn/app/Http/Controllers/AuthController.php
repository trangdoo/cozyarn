<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\UserService;
use App\Support\ClientPasswordNormalizer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect('/');
        }

        // Cho phép link "Đăng nhập" từ trang sản phẩm (?intended=...) đặt URL trở về
        // sau khi đăng nhập thành công — chỉ chấp nhận URL nội bộ để tránh open redirect.
        $intended = (string) $request->query('intended', '');
        if ($intended !== '' && str_starts_with($intended, '/')) {
            $request->session()->put('url.intended', url($intended));
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
        $email    = (string) $request->input('email');
        $remember = (bool) $request->input('remember', false);

        // Client băm SHA-256 trước khi gửi (xem auth-validate.js). Nếu JS không
        // chạy được, server tự băm để giữ pipeline thống nhất với DB.
        $password = ClientPasswordNormalizer::normalize((string) $request->input('password'));

        // Báo lỗi rõ ràng hơn khi tài khoản bị khoá
        $user = $this->users->findByEmail($email);
        if ($user && !$this->users->isActive($user)) {
            return back()
                ->withErrors(['email' => 'Tài khoản đã bị khoá. Vui lòng liên hệ shop để biết thêm chi tiết.'])
                ->withInput($request->only('email'));
        }

        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
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
        $user = $this->users->create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
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

    public function verifyForgotEmail(ForgotPasswordRequest $request)
    {
        $data = $request->validated();
        $user = $this->users->findByEmail($data['email']);

        if (!$user) {
            return back()
                ->withErrors(['email' => 'Không tìm thấy tài khoản với email này.'])
                ->withInput();
        }

        if (!$this->users->isActive($user)) {
            return back()
                ->withErrors(['email' => 'Tài khoản đã bị khoá. Vui lòng liên hệ shop.'])
                ->withInput();
        }

        // Lưu email đã xác thực vào session, cho phép sang bước đặt lại mật khẩu
        $request->session()->put('password_reset_email', $user->email);
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

    public function resetPassword(ResetPasswordRequest $request)
    {
        $email   = $request->session()->get('password_reset_email');
        $expires = $request->session()->get('password_reset_expires_at');

        if (!$email || !$expires || now()->gt($expires)) {
            $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thử lại.']);
        }

        $data = $request->validated();

        try {
            $this->users->resetPasswordByEmail($email, $data['password']);
        } catch (\RuntimeException $e) {
            $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);
            return redirect()->route('password.forgot')
                ->withErrors(['email' => $e->getMessage()]);
        }

        $request->session()->forget(['password_reset_email', 'password_reset_expires_at']);

        return redirect()->route('login')
            ->with('cart_flash', 'Đổi mật khẩu thành công. Mời bạn đăng nhập.');
    }
}
