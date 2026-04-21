<?php

namespace App\Http\Controllers;

use App\Support\Cart;
use Illuminate\Http\Request;

class CartController
{
    public function index()
    {
        return view('user.cart.index', [
            'items'    => Cart::items(),
            'subtotal' => Cart::subtotal(),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'slug'     => 'required|string',
            'name'     => 'required|string',
            'image'    => 'nullable|string',
            'price'    => 'required|integer|min:0',
            'qty'      => 'nullable|integer|min:1|max:99',
            'variant'  => 'nullable|string',
            'size'     => 'nullable|string',
        ]);

        $qty = (int) ($data['qty'] ?? 1);
        $key = Cart::makeKey($data['category'], $data['slug'], $data['variant'] ?? null, $data['size'] ?? null);

        Cart::add($key, [
            'category' => $data['category'],
            'slug'     => $data['slug'],
            'name'     => $data['name'],
            'image'    => $data['image'] ?? null,
            'price'    => (int) $data['price'],
            'variant'  => $data['variant'] ?? null,
            'size'     => $data['size'] ?? null,
        ], $qty);

        return back()->with('cart_flash', 'Đã thêm "' . $data['name'] . '" vào giỏ.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:0|max:99',
        ]);
        Cart::update($data['key'], (int) $data['qty']);
        return back();
    }

    public function remove(Request $request)
    {
        $data = $request->validate(['key' => 'required|string']);
        Cart::remove($data['key']);
        return back()->with('cart_flash', 'Đã xoá sản phẩm khỏi giỏ.');
    }

    public function clear()
    {
        Cart::clear();
        return back()->with('cart_flash', 'Đã xoá toàn bộ giỏ hàng.');
    }
}
