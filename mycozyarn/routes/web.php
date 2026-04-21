<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('login', [AuthController::class,'showLoginForm'])->name('login');
Route::post('login', [AuthController::class,'login']);
Route::get('register', [AuthController::class,'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class,'register']);
Route::post('logout', [AuthController::class,'logout'])->name('logout');

Route::get('/', fn () => view('user.home.index'));

Route::get('/chinh-sach/{slug}', function (string $slug) {
    $policies = require resource_path('policies.php');
    abort_unless(isset($policies[$slug]), 404);
    return view('user.policy.show', [
        'policy' => $policies[$slug],
        'allPolicies' => $policies,
    ]);
})->where('slug', '[a-z\-]+')->name('policy.show');

Route::get('/shop', function () {
    $shop = require resource_path('shop.php');
    return view('user.shop.index', [
        'categories' => $shop['categories'],
        'products'   => $shop['products'],
    ]);
})->name('shop.index');

Route::get('/shop/{category}', function (string $category) {
    $shop = require resource_path('shop.php');
    abort_unless(isset($shop['categories'][$category]), 404);
    return view('user.shop.category', [
        'category'   => $shop['categories'][$category],
        'products'   => $shop['products'][$category] ?? [],
        'categories' => $shop['categories'],
    ]);
})->where('category', '[a-z\-]+')->name('shop.category');

Route::get('/shop/{category}/{product}', function (string $category, string $product) {
    $shop = require resource_path('shop.php');
    abort_unless(isset($shop['categories'][$category]), 404);
    $list = $shop['products'][$category] ?? [];
    $found = collect($list)->firstWhere('slug', $product);
    abort_unless($found, 404);
    return view('user.shop.product', [
        'category' => $shop['categories'][$category],
        'product'  => $found,
        'related'  => collect($list)->where('slug', '!=', $product)->take(4)->values()->all(),
    ]);
})->where(['category' => '[a-z\-]+', 'product' => '[a-z\-]+'])->name('shop.product');
