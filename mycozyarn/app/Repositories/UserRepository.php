<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly User $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $q      = trim((string) ($filters['q'] ?? ''));
        $role   = $filters['role']   ?? 'all';
        $status = $filters['status'] ?? 'all';

        $query = $this->model->newQuery()->orderByDesc('created_at');

        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($role !== 'all' && $role !== null && $role !== '') {
            $query->where('role', $role);
        }
        if ($status !== 'all' && $status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findById(int $id): ?User
    {
        return $this->model->newQuery()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()
            ->where('email', strtolower(trim($email)))
            ->first();
    }

    public function create(array $data): User
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->fill($data)->save();
        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function toggleStatus(User $user): User
    {
        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();
        return $user;
    }

    public function stats(): array
    {
        return [
            'total'   => $this->model->newQuery()->count(),
            'admin'   => $this->model->newQuery()->where('role', 'admin')->count(),
            'active'  => $this->model->newQuery()->where('status', 'active')->count(),
            'blocked' => $this->model->newQuery()->where('status', 'blocked')->count(),
        ];
    }
}
