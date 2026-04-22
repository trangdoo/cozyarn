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
use App\Http\Controllers\Admin\DashboardController    as AdminDashboard;
use App\Http\Controllers\Admin\UserController         as AdminUser;
use App\Http\Controllers\Admin\ProductController      as AdminProduct;
use App\Http\Controllers\Admin\CategoryController     as AdminCategory;
use App\Http\Controllers\Admin\BlogController         as AdminBlog;
use App\Http\Controllers\Admin\OrderController        as AdminOrder;
use App\Http\Controllers\Admin\ChatController         as AdminChat;
use App\Http\Controllers\Admin\NotificationController as AdminNotification;

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

/* ═════════════════════════════════════ ADMIN ═════════════════════════════════════ */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

    // Users
    Route::get('tai-khoan',                 [AdminUser::class, 'index'])->name('users.index');
    Route::get('tai-khoan/{user}',          [AdminUser::class, 'show'])->name('users.show');
    Route::patch('tai-khoan/{user}',        [AdminUser::class, 'update'])->name('users.update');
    Route::delete('tai-khoan/{user}',       [AdminUser::class, 'destroy'])->name('users.destroy');
    Route::post('tai-khoan/{user}/khoa',    [AdminUser::class, 'toggleBlock'])->name('users.toggleBlock');

    // Products — full-feature CRUD + import/export + bulk + duplicate
    Route::get('san-pham',                              [AdminProduct::class, 'index'])->name('products.index');
    Route::get('san-pham/tao',                          [AdminProduct::class, 'create'])->name('products.create');
    Route::post('san-pham',                             [AdminProduct::class, 'store'])->name('products.store');
    Route::get('san-pham/nhap',                         [AdminProduct::class, 'importForm'])->name('products.importForm');
    Route::post('san-pham/nhap',                        [AdminProduct::class, 'import'])->name('products.import');
    Route::get('san-pham/xuat/{format}',                [AdminProduct::class, 'export'])
        ->where('format', 'csv|json|xml')->name('products.export');
    Route::post('san-pham/xoa-nhieu',                   [AdminProduct::class, 'bulkDelete'])->name('products.bulkDelete');
    Route::post('san-pham/sao-chep-nhieu',              [AdminProduct::class, 'duplicateMany'])->name('products.duplicateMany');
    Route::get('san-pham/{category}/{slug}/chi-tiet',   [AdminProduct::class, 'show'])->name('products.show');
    Route::get('san-pham/{category}/{slug}/sua',        [AdminProduct::class, 'edit'])->name('products.edit');
    Route::patch('san-pham/{category}/{slug}',          [AdminProduct::class, 'update'])->name('products.update');
    Route::delete('san-pham/{category}/{slug}',         [AdminProduct::class, 'destroy'])->name('products.destroy');
    Route::post('san-pham/{category}/{slug}/sao-chep',  [AdminProduct::class, 'duplicate'])->name('products.duplicate');

    // Categories
    Route::get('danh-muc',                  [AdminCategory::class, 'index'])->name('categories.index');
    Route::get('danh-muc/tao',              [AdminCategory::class, 'create'])->name('categories.create');
    Route::post('danh-muc',                 [AdminCategory::class, 'store'])->name('categories.store');
    Route::get('danh-muc/{slug}/sua',       [AdminCategory::class, 'edit'])->name('categories.edit');
    Route::patch('danh-muc/{slug}',         [AdminCategory::class, 'update'])->name('categories.update');
    Route::delete('danh-muc/{slug}',        [AdminCategory::class, 'destroy'])->name('categories.destroy');

    // Blog
    Route::get('blog',                      [AdminBlog::class, 'index'])->name('blog.index');
    Route::get('blog/tao',                  [AdminBlog::class, 'create'])->name('blog.create');
    Route::post('blog',                     [AdminBlog::class, 'store'])->name('blog.store');
    Route::post('blog/xoa-nhieu',           [AdminBlog::class, 'bulkDelete'])->name('blog.bulkDelete');
    Route::get('blog/{slug}/chi-tiet',      [AdminBlog::class, 'show'])->name('blog.show');
    Route::get('blog/{slug}/sua',           [AdminBlog::class, 'edit'])->name('blog.edit');
    Route::patch('blog/{slug}',             [AdminBlog::class, 'update'])->name('blog.update');
    Route::delete('blog/{slug}',            [AdminBlog::class, 'destroy'])->name('blog.destroy');
    Route::post('blog/{slug}/noi-bat',      [AdminBlog::class, 'toggleFeatured'])->name('blog.featured');

    // Orders
    Route::get('don-hang',                  [AdminOrder::class, 'index'])->name('orders.index');
    Route::get('don-hang/{id}',             [AdminOrder::class, 'show'])->name('orders.show');
    Route::patch('don-hang/{id}/trang-thai',[AdminOrder::class, 'updateStatus'])->name('orders.status');

    // Chat
    Route::get('tin-nhan',                      [AdminChat::class, 'index'])->name('chat.index');
    Route::get('tin-nhan/{threadId}',           [AdminChat::class, 'show'])->name('chat.show');
    Route::post('tin-nhan/{threadId}/tra-loi',  [AdminChat::class, 'reply'])->name('chat.reply');
    Route::post('tin-nhan/{threadId}/ghim',     [AdminChat::class, 'togglePin'])->name('chat.pin');
    Route::post('tin-nhan/{threadId}/tat-tb',   [AdminChat::class, 'toggleMute'])->name('chat.mute');
    Route::delete('tin-nhan/{threadId}',        [AdminChat::class, 'destroy'])->name('chat.destroy');

    // Notifications
    Route::get('thong-bao',                 [AdminNotification::class, 'index'])->name('notifications.index');
    Route::get('thong-bao/tao',             [AdminNotification::class, 'create'])->name('notifications.create');
    Route::post('thong-bao',                [AdminNotification::class, 'store'])->name('notifications.store');
    Route::post('thong-bao/xoa-nhieu',      [AdminNotification::class, 'bulkDelete'])->name('notifications.bulkDelete');
    Route::get('thong-bao/{id}/sua',        [AdminNotification::class, 'edit'])->name('notifications.edit');
    Route::patch('thong-bao/{id}',          [AdminNotification::class, 'update'])->name('notifications.update');
    Route::delete('thong-bao/{id}',         [AdminNotification::class, 'destroy'])->name('notifications.destroy');
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
