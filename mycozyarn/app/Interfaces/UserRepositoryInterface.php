<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Hợp đồng truy cập dữ liệu User.
 * Service tầng trên chỉ phụ thuộc interface này, dễ thay implementation
 * (Eloquent / fake in-memory cho test).
 */
interface UserRepositoryInterface
{
    /**
     * Danh sách user kèm phân trang + lọc cho trang admin.
     *
     * @param array{q?:string|null, role?:string|null, status?:string|null} $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * Tạo user mới. Caller có trách nhiệm normalize input;
     * password sẽ tự hash qua cast của model.
     */
    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    /** Đổi trạng thái active <-> blocked, lưu DB. */
    public function toggleStatus(User $user): User;

    /**
     * Số liệu tổng hợp cho header trang admin: total / admin / active / blocked.
     *
     * @return array{total:int, admin:int, active:int, blocked:int}
     */
    public function stats(): array;
}
