<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;

Route::get('login', [AuthController::class,'showLoginForm'])->name('login');
Route::post('login', [AuthController::class,'login']);
Route::get('register', [AuthController::class,'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class,'register']);
Route::post('logout', [AuthController::class,'logout'])->name('logout');

Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/them', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-hang', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/tat-ca', [CartController::class, 'clear'])->name('cart.clear');
Route::delete('/gio-hang', [CartController::class, 'remove'])->name('cart.remove');

Route::post('/thanh-toan/bat-dau',      [CheckoutController::class, 'start'])->name('checkout.start');
Route::post('/mua-ngay',                [CheckoutController::class, 'buyNow'])->name('checkout.buyNow');
Route::get('/thanh-toan',               [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/thanh-toan',              [CheckoutController::class, 'place'])->name('checkout.place');
Route::get('/dat-hang-thanh-cong/{id}', [CheckoutController::class, 'success'])
    ->where('id', '[A-Z0-9]+')->name('checkout.success');

Route::middleware('auth')->group(function () {
    Route::get('/tai-khoan',           [UserController::class, 'profile'])->name('user.profile');
    Route::patch('/tai-khoan',         [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::patch('/tai-khoan/mat-khau',[UserController::class, 'updatePassword'])->name('user.password.update');
    Route::get('/don-hang',            [UserController::class, 'orders'])->name('user.orders');
    Route::get('/don-hang/hoan-tat',   [UserController::class, 'completedOrders'])->name('user.orders.completed');
    Route::get('/don-hang/da-huy',     [UserController::class, 'cancelledOrders'])->name('user.orders.cancelled');
    Route::get('/don-hang/tra-hang',   [UserController::class, 'returnedOrders'])->name('user.orders.returned');
    Route::get('/don-hang/{id}',       [UserController::class, 'orderShow'])
        ->where('id', '[A-Z0-9]+')->name('user.orders.show');
    Route::post('/don-hang/{id}/huy',         [UserController::class, 'cancelOrder'])
        ->where('id', '[A-Z0-9]+')->name('user.orders.cancel');
    Route::post('/don-hang/{id}/tra-hang',    [UserController::class, 'requestReturn'])
        ->where('id', '[A-Z0-9]+')->name('user.orders.return');
    Route::post('/don-hang/{id}/xac-nhan',    [UserController::class, 'confirmReceived'])
        ->where('id', '[A-Z0-9]+')->name('user.orders.confirm');

    Route::get('/danh-gia-cua-toi',    [ReviewController::class, 'myReviews'])->name('user.reviews');
    Route::post('/danh-gia',           [ReviewController::class, 'store'])->name('user.reviews.store');
    Route::delete('/danh-gia',         [ReviewController::class, 'destroy'])->name('user.reviews.destroy');

    Route::get('/tin-nhan',            [ChatController::class, 'inbox'])->name('user.chat.inbox');
    Route::get('/tin-nhan/{threadId}', [ChatController::class, 'thread'])
        ->where('threadId', '[a-zA-Z0-9\-_]+')->name('user.chat.thread');
    Route::post('/tin-nhan/gui',       [ChatController::class, 'send'])->name('user.chat.send');

    Route::post('/blog/{slug}/tim',     [BlogController::class, 'toggleLike'])
        ->where('slug', '[a-z0-9\-]+')->name('blog.like');
    Route::get('/bai-viet-da-tim',      [BlogController::class, 'liked'])->name('blog.liked');

    Route::get('/thong-bao',                [NotificationController::class, 'index'])->name('user.notifications.index');
    Route::get('/thong-bao/{id}',           [NotificationController::class, 'open'])
        ->where('id', '[A-Z0-9\-]+')->name('user.notifications.open');
    Route::post('/thong-bao/doc-tat-ca',    [NotificationController::class, 'markAllRead'])->name('user.notifications.readAll');
});

Route::get('/', function () {
    $shop = require resource_path('shop.php');

    // Sản phẩm featured cho section Bestseller ở trang home.
    // Mỗi dòng: category (khoá trong shop.php), slug, badge hiển thị, data-cat cho tab filter
    $selections = [
        ['category' => 'len-soi',     'slug' => 'len-cotton-pastel',  'badge' => 'Hot',  'data_cat' => 'yarn'],
        ['category' => 'len-soi',     'slug' => 'len-gradient-ombre', 'badge' => 'Mới',  'data_cat' => 'yarn'],
        ['category' => 'kim-moc',     'slug' => 'kim-dan-inox-9',     'badge' => null,   'data_cat' => 'tools'],
        ['category' => 'starter-kit', 'slug' => 'kit-gau-bong',       'badge' => 'Sale', 'data_cat' => 'kits'],
        ['category' => 'len-soi',     'slug' => 'len-mohair-anh-kim', 'badge' => null,   'data_cat' => 'yarn'],
        ['category' => 'kim-moc',     'slug' => 'moc-ergonomic-go',   'badge' => 'Hot',  'data_cat' => 'tools'],
    ];

    $featured = [];
    foreach ($selections as $sel) {
        $list  = $shop['products'][$sel['category']] ?? [];
        $found = collect($list)->firstWhere('slug', $sel['slug']);
        if ($found) {
            $featured[] = [
                ...$found,
                'category_slug' => $sel['category'],
                'badge'         => $sel['badge'],
                'data_cat'      => $sel['data_cat'],
            ];
        }
    }

    return view('user.home.index', ['featured' => $featured]);
});

Route::get('/blog',                 [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}',          [BlogController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')->name('blog.show');

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
})->where('category', '[a-z0-9\-]+')->name('shop.category');

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
})->where(['category' => '[a-z0-9\-]+', 'product' => '[a-z0-9\-]+'])->name('shop.product');
