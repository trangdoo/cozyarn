<?php

namespace App\Http\Controllers;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;


class AuthController extends Controller{
    public function showLoginForm()  {
        return view('user.auth.login');
    }
    public function showRegisterForm()  {
        return view('user.auth.register');
    }
    public function login(LoginRequest $req)
    {
        $cre = $req -> only('email','password');
        $remember =(bool)$req->input('remember',false);
        if(Auth::attempt($cre,$remember))
            {
                $req->session() -> regenerate();
                return redirect()->intended('/');
            }
    return back()->withErrors(['email'=>'Email hoặc mật khẩu không đúng'])->withInput();
    }

    public function register(RegisterRequest $request){
    $user = User::create([
      'name'=>$request->name,
      'email'=>$request->email,
      'password'=>Hash::make($request->password),
    ]);
    Auth::login($user);
    return redirect('/');
  }

  public function logout(Request $request){
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('user.home');
  }
}