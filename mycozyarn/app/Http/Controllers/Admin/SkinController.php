<?php

namespace App\Http\Controllers\Admin;

use App\Support\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Admin → Hệ thống → Giao diện (Skin).
 * Cho phép quản trị viên chuyển đổi skin của shop tại runtime.
 */
class SkinController extends Controller
{
    public function index()
    {
        return view('admin.skin.index', [
            'themes'       => ThemeManager::all(),
            'activeTheme'  => ThemeManager::active(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme' => 'required|string|max:30',
        ]);

        if (!ThemeManager::setActive($data['theme'])) {
            return back()->withErrors(['theme' => 'Skin không tồn tại.']);
        }

        $meta = ThemeManager::meta($data['theme']);
        return back()->with('cart_flash', "Đã đổi skin sang \"{$meta['name']}\".");
    }
}
