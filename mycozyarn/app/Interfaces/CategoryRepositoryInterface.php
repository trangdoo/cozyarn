<?php

namespace App\Interfaces;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Hợp đồng truy cập dữ liệu Category. Service tầng trên chỉ phụ thuộc interface này.
 */
interface CategoryRepositoryInterface
{
    /** Toàn bộ danh mục (sort theo tên), kèm số sản phẩm mỗi danh mục. */
    public function all(): Collection;

    /** Danh sách phân trang + lọc theo q (tên/slug/desc). */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): bool;

    /** True nếu slug đã tồn tại (loại trừ id nếu có). */
    public function slugExists(string $slug, ?int $exceptId = null): bool;
}
