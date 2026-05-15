<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly Product $model) {}

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->buildQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allFiltered(array $filters = []): Collection
    {
        return $this->buildQuery($filters)->get();
    }

    public function findManyByIds(array $ids): Collection
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->model->newQuery()
            ->with('category')
            ->whereIn('id', $ids)
            ->get();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->newQuery()->with('category')->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model->newQuery()->with('category')->where('slug', $slug)->first();
    }

    public function findByCategoryAndSlug(string $categorySlug, string $productSlug): ?Product
    {
        return $this->model->newQuery()
            ->with('category')
            ->whereHas('category', fn ($q) => $q->where('slug', $categorySlug))
            ->where('slug', $productSlug)
            ->first();
    }

    public function create(array $data): Product
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->fill($data)->save();
        return $product->refresh()->load('category');
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function deleteMany(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) return 0;

        return $this->model->newQuery()->whereIn('id', $ids)->delete();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $query = $this->model->newQuery()->where('slug', $slug);
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        return $query->exists();
    }

    /* ─────────────── Internal ─────────────── */

    private function buildQuery(array $filters): Builder
    {
        $q        = trim((string) ($filters['q'] ?? ''));
        $category = $filters['category'] ?? 'all';
        $status   = $filters['status']   ?? 'all';
        $sort     = $filters['sort']     ?? 'updated_desc';

        $query = $this->model->newQuery()->with('category');

        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('slug', 'like', "%{$q}%")
                  ->orWhere('short_desc', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($category !== 'all' && $category !== null && $category !== '') {
            $query->whereHas('category', fn ($b) => $b->where('slug', $category));
        }
        if ($status !== 'all' && $status !== null && $status !== '') {
            $query->where('status', $status);
        }

        match ($sort) {
            'name_asc'     => $query->orderBy('name'),
            'name_desc'    => $query->orderByDesc('name'),
            'price_asc'    => $query->orderBy('price'),
            'price_desc'   => $query->orderByDesc('price'),
            'created_desc' => $query->orderByDesc('created_at'),
            default        => $query->orderByDesc('updated_at'),
        };

        return $query;
    }
}
