<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $shop = require resource_path('shop.php');
        $blog = require resource_path('blog.php');

        // Đếm sản phẩm từ file hardcode + overlay session (nếu có)
        $productsFromFile = 0;
        foreach ($shop['products'] as $list) $productsFromFile += \count($list);
        $productsFromFile += \count(session('admin_products_added', []));

        $stats = [
            'users'    => User::count(),
            'products' => $productsFromFile,
            'blogs'    => \count($blog['posts']) + \count(session('admin_blogs_added', [])),
            'orders'   => \count(session('orders', [])), // demo — chỉ session admin
            'revenue'  => array_sum(array_map(fn($o) => (int) ($o['total'] ?? 0), session('orders', []))),
        ];

        // Latest orders (session) — 5 đơn gần nhất
        $recentOrders = array_values(session('orders', []));
        usort($recentOrders, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentOrders = array_slice($recentOrders, 0, 5);

        // Latest users
        $recentUsers = User::orderByDesc('created_at')->limit(5)->get();

        return view('admin.dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
            'recentUsers'  => $recentUsers,
        ]);
    }
}
