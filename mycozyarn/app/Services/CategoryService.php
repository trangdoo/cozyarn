<?php

namespace App\Services;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    /* ─────────────── Read ─────────────── */

    public function all(): Collection
    {
        return $this->categories->all();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->categories->paginate($filters, $perPage);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->categories->findBySlug($slug);
    }

    /** Trả về Collection keyed theo slug — tiện cho dropdown / lookup trong view. */
    public function keyedBySlug(): Collection
    {
        return $this->categories->all()->keyBy('slug');
    }

    /* ─────────────── Write ─────────────── */

    public function create(array $data): Category
    {
        $payload = [
            'name'        => trim((string) ($data['name'] ?? '')),
            'description' => $this->nullableString($data['description'] ?? null, 500),
            'image'       => $this->nullableString($data['image'] ?? null, 255),
        ];

        $payload['slug'] = $this->uniqueSlug($payload['name']);

        return $this->categories->create($payload);
    }

    public function update(Category $category, array $data): Category
    {
        $payload = [
            'name'        => trim((string) ($data['name'] ?? $category->name)),
            'description' => $this->nullableString($data['description'] ?? null, 500),
            'image'       => $this->nullableString($data['image'] ?? null, 255),
        ];

        // Cho phép admin tự đổi slug; nếu không gửi → giữ nguyên slug hiện tại.
        if (!empty($data['slug'])) {
            $newSlug = Str::slug((string) $data['slug']);
            if ($newSlug !== $category->slug && $this->categories->slugExists($newSlug, $category->id)) {
                throw new RuntimeException('Slug đã được sử dụng bởi danh mục khác.');
            }
            $payload['slug'] = $newSlug;
        }

        return $this->categories->update($category, $payload);
    }

    /**
     * Xoá danh mục. Nếu còn sản phẩm thuộc danh mục → từ chối để tránh mất dữ liệu;
     * admin nên chuyển sản phẩm sang danh mục khác trước.
     */
    public function delete(Category $category): void
    {
        $count = $category->products()->count();
        if ($count > 0) {
            throw new RuntimeException("Không thể xoá: còn {$count} sản phẩm thuộc danh mục này.");
        }
        $this->categories->delete($category);
    }

    /* ─────────────── Helpers ─────────────── */

    /**
     * Tạo slug duy nhất từ tên — nếu trùng, gắn thêm hậu tố ngẫu nhiên.
     */
    public function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'danh-muc';
        $slug = $base;
        $i = 1;
        while ($this->categories->slugExists($slug, $exceptId)) {
            $slug = $base . '-' . (++$i);
            if ($i > 50) {
                $slug = $base . '-' . substr((string) Str::uuid(), 0, 6);
                break;
            }
        }
        return $slug;
    }

    private function nullableString(mixed $value, int $max): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        if ($value === '') return null;
        return mb_substr($value, 0, $max);
    }
}
