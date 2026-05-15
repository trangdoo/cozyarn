<?php

namespace App\Interfaces;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Hợp đồng truy cập dữ liệu Product. Service tầng trên chỉ phụ thuộc interface này,
 * dễ thay implementation (Eloquent / fake in-memory cho test).
 */
interface ProductRepositoryInterface
{
    /**
     * Danh sách sản phẩm phân trang + lọc cho trang admin.
     *
     * @param array{q?:string|null, category?:string|null, status?:string|null, sort?:string|null} $filters
     */
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    /** Toàn bộ sản phẩm khớp filters (không phân trang) — dùng cho export. */
    public function allFiltered(array $filters = []): Collection;

    /** Lấy theo danh sách id (preserve eager-load) — dùng cho bulk action / export selection. */
    public function findManyByIds(array $ids): Collection;

    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function findByCategoryAndSlug(string $categorySlug, string $productSlug): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    /** Xoá theo id list, trả về số bản ghi thực sự xoá. */
    public function deleteMany(array $ids): int;

    /** True nếu slug đã tồn tại (loại trừ id nếu có). */
    public function slugExists(string $slug, ?int $exceptId = null): bool;
}
