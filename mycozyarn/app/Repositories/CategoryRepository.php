<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly Category $model) {}

    public function all(): Collection
    {
        return $this->model->newQuery()
            ->withCount('products')
            ->orderBy('name')
            ->get();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));

        $query = $this->model->newQuery()->withCount('products')->orderBy('name');

        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('slug', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findById(int $id): ?Category
    {
        return $this->model->newQuery()->find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->newQuery()->where('slug', $slug)->first();
    }

    public function create(array $data): Category
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->fill($data)->save();
        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $query = $this->model->newQuery()->where('slug', $slug);
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        return $query->exists();
    }
}
