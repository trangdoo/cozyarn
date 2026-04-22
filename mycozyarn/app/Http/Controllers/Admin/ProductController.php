<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * Admin products — frontend demo CRUD.
 * Đọc từ resources/shop.php + overlay session:
 *   session('admin_products_added')  — sản phẩm mới tạo
 *   session('admin_products_edited') — key=catSlug+prodSlug, value=overlay fields
 *   session('admin_products_deleted') — danh sách "catSlug::prodSlug" đã xoá
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $all = $this->allProducts();

        $q        = trim((string) $request->query('q', ''));
        $category = $request->query('category', 'all');
        $status   = $request->query('status', 'all');

        $filtered = array_filter($all, function ($p) use ($q, $category, $status) {
            if ($q !== '' && !str_contains(mb_strtolower($p['name']), mb_strtolower($q))) return false;
            if ($category !== 'all' && $p['category_slug'] !== $category) return false;
            if ($status !== 'all' && ($p['status'] ?? 'active') !== $status) return false;
            return true;
        });

        $shop = require resource_path('shop.php');

        return view('admin.products.index', [
            'products'   => array_values($filtered),
            'categories' => $shop['categories'],
            'filter'     => compact('q', 'category', 'status'),
        ]);
    }

    public function create()
    {
        $shop = require resource_path('shop.php');
        return view('admin.products.form', [
            'categories' => $shop['categories'],
            'product'    => null,
        ]);
    }

    public function edit(string $category, string $slug)
    {
        $all = $this->allProducts();
        $product = collect($all)->first(fn($p) => $p['category_slug'] === $category && $p['slug'] === $slug);
        abort_unless($product, 404);

        $shop = require resource_path('shop.php');
        return view('admin.products.form', [
            'categories' => $shop['categories'],
            'product'    => $product,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $added = session('admin_products_added', []);
        $data['slug'] = Str::slug($data['name']) . '-' . substr((string) Str::uuid(), 0, 6);
        $added[] = $data;
        session(['admin_products_added' => $added]);

        return redirect()->route('admin.products.index')->with('cart_flash', 'Đã thêm sản phẩm mới.');
    }

    public function update(Request $request, string $category, string $slug)
    {
        $data = $this->validateData($request);
        $edited = session('admin_products_edited', []);
        $key = "{$category}::{$slug}";
        $edited[$key] = $data;
        session(['admin_products_edited' => $edited]);

        return redirect()->route('admin.products.index')->with('cart_flash', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(string $category, string $slug)
    {
        $deleted = session('admin_products_deleted', []);
        $deleted[] = "{$category}::{$slug}";
        session(['admin_products_deleted' => array_unique($deleted)]);

        // Nếu là sản phẩm do admin tự thêm thì xoá khỏi added
        $added = session('admin_products_added', []);
        $added = array_values(array_filter($added, fn($p) =>
            !($p['category_slug'] === $category && $p['slug'] === $slug)
        ));
        session(['admin_products_added' => $added]);

        return back()->with('cart_flash', 'Đã xoá sản phẩm.');
    }

    /* ═══════════════════ helpers ═══════════════════ */

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'          => 'required|string|max:200',
            'category_slug' => 'required|string|max:80',
            'shortDesc'     => 'required|string|max:500',
            'desc'          => 'nullable|string|max:3000',
            'price'         => 'required|integer|min:0',
            'oldPrice'      => 'nullable|integer|min:0',
            'image'         => 'nullable|string|max:300',
            'tag'           => 'nullable|string|max:30',
            'status'        => 'required|in:active,inactive',
        ]);
    }

    private function allProducts(): array
    {
        $shop    = require resource_path('shop.php');
        $deleted = session('admin_products_deleted', []);
        $edited  = session('admin_products_edited', []);
        $added   = session('admin_products_added', []);

        $result = [];
        foreach ($shop['products'] as $catSlug => $list) {
            foreach ($list as $p) {
                $key = "{$catSlug}::{$p['slug']}";
                if (\in_array($key, $deleted, true)) continue;
                $p['category_slug'] = $catSlug;
                if (isset($edited[$key])) {
                    $p = array_merge($p, $edited[$key]);
                }
                $result[] = $p;
            }
        }
        foreach ($added as $p) {
            if (!isset($p['category_slug'])) continue;
            $result[] = $p;
        }
        return $result;
    }
}
