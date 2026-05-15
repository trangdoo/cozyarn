<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\RemoveFromCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Support\Cart;

class CartController
{
    public function index()
    {
        return view('user.cart.index', [
            'items'    => Cart::items(),
            'subtotal' => Cart::subtotal(),
        ]);
    }

    public function add(AddToCartRequest $request)
    {
        $data = $request->validated();
        $qty  = (int) ($data['qty'] ?? 1);
        $key  = Cart::makeKey($data['category'], $data['slug'], $data['variant'] ?? null, $data['size'] ?? null);

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

    public function update(UpdateCartRequest $request)
    {
        $data = $request->validated();
        Cart::update($data['key'], (int) $data['qty']);
        return back();
    }

    public function remove(RemoveFromCartRequest $request)
    {
        Cart::remove($request->validated()['key']);
        return back()->with('cart_flash', 'Đã xoá sản phẩm khỏi giỏ.');
    }

    public function clear()
    {
        Cart::clear();
        return back()->with('cart_flash', 'Đã xoá toàn bộ giỏ hàng.');
    }
}
