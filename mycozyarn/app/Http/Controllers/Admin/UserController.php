<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $role   = $request->query('role', 'all');
        $status = $request->query('status', 'all');

        $query = User::query()->orderByDesc('created_at');
        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($role !== 'all')   $query->where('role', $role);
        if ($status !== 'all') $query->where('status', $status);

        return view('admin.users.index', [
            'users'  => $query->paginate(15)->withQueryString(),
            'filter' => compact('q', 'role', 'status'),
            'stats'  => [
                'total'   => User::count(),
                'admin'   => User::where('role', 'admin')->count(),
                'active'  => User::where('status', 'active')->count(),
                'blocked' => User::where('status', 'blocked')->count(),
            ],
        ]);
    }

    public function show(User $user)
    {
        return view('admin.users.show', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => "required|email|max:150|unique:users,email,{$user->id}",
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:300',
            'role'    => 'required|in:user,admin',
            'status'  => 'required|in:active,blocked',
        ]);

        $user->update($data);
        return back()->with('cart_flash', 'Đã cập nhật tài khoản.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('cart_flash', 'Không thể xoá chính tài khoản đang đăng nhập.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('cart_flash', 'Đã xoá tài khoản.');
    }

    public function toggleBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('cart_flash', 'Không thể thay đổi trạng thái của chính bạn.');
        }
        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();
        return back()->with('cart_flash', $user->status === 'blocked' ? 'Đã khoá tài khoản.' : 'Đã mở khoá tài khoản.');
    }
}
