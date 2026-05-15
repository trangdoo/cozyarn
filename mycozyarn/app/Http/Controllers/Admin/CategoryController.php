<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(Request $request)
    {
        $filters = ['q' => trim((string) $request->query('q', ''))];

        return view('admin.categories.index', [
            'categories' => $this->categories->paginate($filters, 24),
            'filter'     => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categories->create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('cart_flash', "Đã tạo danh mục {$category->name}.");
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $this->categories->update($category, $request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('cart_flash', $e->getMessage());
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('cart_flash', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        try {
            $this->categories->delete($category);
        } catch (RuntimeException $e) {
            return back()->with('cart_flash', $e->getMessage());
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('cart_flash', 'Đã xoá danh mục.');
    }
}
