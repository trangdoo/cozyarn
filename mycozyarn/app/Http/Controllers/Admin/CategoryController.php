<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => $this->allCategories(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function edit(string $slug)
    {
        $cats = $this->allCategories();
        abort_unless(isset($cats[$slug]), 404);
        return view('admin.categories.form', ['category' => $cats[$slug]]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);

        $added = session('admin_categories_added', []);
        $added[$data['slug']] = $data;
        session(['admin_categories_added' => $added]);

        return redirect()->route('admin.categories.index')->with('cart_flash', 'Đã thêm danh mục.');
    }

    public function update(Request $request, string $slug)
    {
        $data = $this->validateData($request);
        $edited = session('admin_categories_edited', []);
        $edited[$slug] = $data;
        session(['admin_categories_edited' => $edited]);

        return redirect()->route('admin.categories.index')->with('cart_flash', 'Đã cập nhật danh mục.');
    }

    public function destroy(string $slug)
    {
        $deleted = session('admin_categories_deleted', []);
        $deleted[] = $slug;
        session(['admin_categories_deleted' => array_unique($deleted)]);

        $added = session('admin_categories_added', []);
        unset($added[$slug]);
        session(['admin_categories_added' => $added]);

        return back()->with('cart_flash', 'Đã xoá danh mục.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'image'       => 'nullable|string|max:300',
        ]);
    }

    private function allCategories(): array
    {
        $shop    = require resource_path('shop.php');
        $added   = session('admin_categories_added', []);
        $edited  = session('admin_categories_edited', []);
        $deleted = session('admin_categories_deleted', []);

        $result = $shop['categories'];
        foreach ($deleted as $slug) unset($result[$slug]);
        foreach ($edited as $slug => $data) {
            if (isset($result[$slug])) {
                $result[$slug] = array_merge($result[$slug], $data);
            }
        }
        foreach ($added as $slug => $data) {
            $result[$slug] = array_merge(['slug' => $slug], $data);
        }
        return $result;
    }
}
