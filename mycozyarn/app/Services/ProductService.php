<?php

namespace App\Services;

use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    /* ─────────────── Read ─────────────── */

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->products->paginate($filters, $perPage);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->products->findBySlug($slug);
    }

    public function findByCategoryAndSlug(string $categorySlug, string $productSlug): ?Product
    {
        return $this->products->findByCategoryAndSlug($categorySlug, $productSlug);
    }

    /**
     * Lấy danh sách sản phẩm để export. Nếu có $ids → chỉ lấy theo ids đó;
     * ngược lại lấy toàn bộ theo filter.
     */
    public function forExport(array $filters = [], array $ids = []): Collection
    {
        if (!empty($ids)) {
            return $this->products->findManyByIds($this->resolveIds($ids));
        }
        return $this->products->allFiltered($filters);
    }

    /* ─────────────── Write ─────────────── */

    public function create(array $data): Product
    {
        $payload = $this->normalizePayload($data, isUpdate: false);
        $payload['slug'] = $this->uniqueSlug($payload['name']);

        return $this->products->create($payload);
    }

    public function update(Product $product, array $data): Product
    {
        $payload = $this->normalizePayload($data, isUpdate: true);

        // Cho phép admin tự đổi slug nếu cần.
        if (!empty($data['slug'])) {
            $newSlug = Str::slug((string) $data['slug']);
            if ($newSlug !== $product->slug && $this->products->slugExists($newSlug, $product->id)) {
                throw new RuntimeException('Slug sản phẩm đã tồn tại.');
            }
            $payload['slug'] = $newSlug;
        }

        return $this->products->update($product, $payload);
    }

    public function delete(Product $product): bool
    {
        return $this->products->delete($product);
    }

    /** Xoá nhiều theo "category::slug" hoặc id thuần. Trả về số bản ghi xoá thực tế. */
    public function deleteMany(array $compositeIds): int
    {
        return $this->products->deleteMany($this->resolveIds($compositeIds));
    }

    /** Tạo bản sao của sản phẩm (slug mới + tên có hậu tố "(Bản sao)"). */
    public function duplicate(Product $product): Product
    {
        $payload = [
            'category_id'    => $product->category_id,
            'name'           => $product->name . ' (Bản sao)',
            'description'    => $product->description,
            'short_desc'     => $product->short_desc,
            'price'          => $product->price,
            'old_price'      => $product->old_price,
            'stock_quantity' => $product->stock_quantity,
            'unit'           => $product->unit,
            'tag'            => $product->tag,
            'thumbnail'      => $product->thumbnail,
            'status'         => $product->status,
        ];
        $payload['slug'] = $this->uniqueSlug($product->name . '-copy');

        return $this->products->create($payload);
    }

    public function duplicateMany(array $compositeIds): int
    {
        $ids = $this->resolveIds($compositeIds);
        if (empty($ids)) return 0;

        $rows = $this->products->findManyByIds($ids);
        $count = 0;
        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                $this->duplicate($row);
                $count++;
            }
        });
        return $count;
    }

    /* ─────────────── Import ─────────────── */

    /**
     * Import danh sách rows (đã parse từ CSV/JSON/XML). Trả về số bản ghi thêm mới.
     * Mỗi row có thể chứa các key: id, name, category, image, unit, quantity, price, description, status.
     */
    public function importRows(array $rows): int
    {
        $count = 0;
        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $r) {
                $name = trim((string) ($r['name'] ?? ''));
                if ($name === '') continue;

                $categorySlug = (string) ($r['category'] ?? '');
                $category     = $categorySlug !== '' ? $this->categories->findBySlug($categorySlug) : null;

                $payload = [
                    'category_id'    => $category?->id,
                    'name'           => $name,
                    'description'    => $this->nullableString($r['description'] ?? null, 3000),
                    'short_desc'     => $this->nullableString($r['description'] ?? null, 500),
                    'price'          => (int) ($r['price'] ?? 0),
                    'old_price'      => null,
                    'stock_quantity' => (int) ($r['quantity'] ?? 0),
                    'unit'           => $this->nullableString($r['unit'] ?? null, 30) ?? 'cuộn',
                    'tag'            => $this->nullableString($r['tag'] ?? null, 30),
                    'thumbnail'      => $this->nullableString($r['image'] ?? null, 255),
                    'status'         => \in_array(($r['status'] ?? null), ['active', 'inactive'], true)
                        ? $r['status']
                        : 'active',
                ];

                $rawSlug = trim((string) ($r['id'] ?? ''));
                $payload['slug'] = $rawSlug !== ''
                    ? $this->uniqueSlug($rawSlug)
                    : $this->uniqueSlug($name);

                $this->products->create($payload);
                $count++;
            }
        });
        return $count;
    }

    /* ─────────────── Helpers ─────────────── */

    /**
     * Chuẩn hoá payload từ form về schema DB. $isUpdate=true cho phép thiếu field
     * không bắt buộc mà không bị set null thành 0.
     *
     * @return array<string,mixed>
     */
    private function normalizePayload(array $data, bool $isUpdate): array
    {
        $categoryId = null;
        if (!empty($data['category_slug'])) {
            $cat = $this->categories->findBySlug((string) $data['category_slug']);
            if (!$cat) {
                throw new RuntimeException('Danh mục không tồn tại.');
            }
            $categoryId = $cat->id;
        } elseif (isset($data['category_id'])) {
            $categoryId = (int) $data['category_id'];
        }

        $payload = array_filter([
            'category_id'    => $categoryId,
            'name'           => isset($data['name']) ? trim((string) $data['name']) : null,
            'description'    => $this->nullableString($data['desc'] ?? $data['description'] ?? null, 3000),
            'short_desc'     => $this->nullableString($data['shortDesc'] ?? $data['short_desc'] ?? null, 500),
            'price'          => isset($data['price']) ? (int) $data['price'] : null,
            'old_price'      => isset($data['oldPrice']) || isset($data['old_price'])
                ? ($this->intOrNull($data['oldPrice'] ?? $data['old_price'] ?? null))
                : null,
            'stock_quantity' => isset($data['quantity']) || isset($data['stock_quantity'])
                ? (int) ($data['quantity'] ?? $data['stock_quantity'])
                : null,
            'unit'           => $this->nullableString($data['unit'] ?? null, 30),
            'tag'            => $this->nullableString($data['tag'] ?? null, 30),
            'thumbnail'      => $this->nullableString($data['image'] ?? $data['thumbnail'] ?? null, 255),
            'status'         => \in_array(($data['status'] ?? null), ['active', 'inactive'], true)
                ? $data['status']
                : null,
        ], static fn ($v) => $v !== null);

        if (!$isUpdate) {
            // Bảo đảm các default ở insert
            $payload['stock_quantity'] ??= 0;
            $payload['unit']           ??= 'cuộn';
            $payload['status']         ??= 'active';
            $payload['thumbnail']      ??= '/images/1.jpg';
        }

        return $payload;
    }

    /**
     * Tạo slug duy nhất từ tên — nếu trùng, gắn thêm hậu tố ngẫu nhiên.
     */
    public function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'san-pham';
        $slug = $base;
        $i = 1;
        while ($this->products->slugExists($slug, $exceptId)) {
            $slug = $base . '-' . (++$i);
            if ($i > 50) {
                $slug = $base . '-' . substr((string) Str::uuid(), 0, 6);
                break;
            }
        }
        return $slug;
    }

    /**
     * Chuẩn hoá danh sách id nhận từ FE: chấp nhận cả "{cat}::{slug}", "{slug}" và id thuần.
     * Trả về mảng id integer.
     *
     * @param array<int,string|int> $rawIds
     * @return int[]
     */
    private function resolveIds(array $rawIds): array
    {
        $resolved = [];
        foreach ($rawIds as $raw) {
            if (is_int($raw) || (is_string($raw) && ctype_digit($raw))) {
                $resolved[] = (int) $raw;
                continue;
            }
            $raw = (string) $raw;
            $slug = str_contains($raw, '::')
                ? (explode('::', $raw, 2)[1] ?? null)
                : $raw;
            if (!$slug) continue;

            $product = $this->products->findBySlug($slug);
            if ($product) $resolved[] = $product->id;
        }
        return array_values(array_unique($resolved));
    }

    private function nullableString(mixed $value, int $max): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        if ($value === '') return null;
        return mb_substr($value, 0, $max);
    }

    private function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') return null;
        return (int) $value;
    }
}
