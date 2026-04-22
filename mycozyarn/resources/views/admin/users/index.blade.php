@extends('layouts.admin')

@section('title', 'Quản lý tài khoản — CozyYarn')
@section('page_title', 'Tài khoản')

@php $active = 'users'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Tài khoản</h1>
            <p>Quản lý {{ $stats['total'] }} tài khoản trong hệ thống</p>
        </div>
    </div>

    <div class="admin-stats admin-stats--compact">
        <div class="admin-stat-sm"><small>Tổng</small><strong>{{ $stats['total'] }}</strong></div>
        <div class="admin-stat-sm"><small>Admin</small><strong>{{ $stats['admin'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đang hoạt động</small><strong>{{ $stats['active'] }}</strong></div>
        <div class="admin-stat-sm"><small>Đã khoá</small><strong>{{ $stats['blocked'] }}</strong></div>
    </div>

    <form method="GET" class="admin-filter">
        <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Tìm theo tên, email, SĐT...">
        <select name="role">
            <option value="all" @selected($filter['role'] === 'all')>Tất cả vai trò</option>
            <option value="user" @selected($filter['role'] === 'user')>User</option>
            <option value="admin" @selected($filter['role'] === 'admin')>Admin</option>
        </select>
        <select name="status">
            <option value="all" @selected($filter['status'] === 'all')>Tất cả trạng thái</option>
            <option value="active" @selected($filter['status'] === 'active')>Hoạt động</option>
            <option value="blocked" @selected($filter['status'] === 'blocked')>Đã khoá</option>
        </select>
        <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
    </form>

    <div class="admin-card">
        <table class="admin-table admin-table--full">
            <thead>
                <tr>
                    <th>Tài khoản</th><th>SĐT</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tham gia</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-user-cell__avatar">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                <div>
                                    <strong>{{ $u->name }}</strong>
                                    <small>{{ $u->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->phone ?: '—' }}</td>
                        <td><span class="admin-badge admin-badge--{{ $u->role }}">{{ $u->role }}</span></td>
                        <td><span class="admin-badge admin-badge--{{ $u->status }}">{{ $u->status === 'active' ? 'Hoạt động' : 'Đã khoá' }}</span></td>
                        <td>{{ $u->created_at->format('d/m/Y') }}</td>
                        <td class="admin-table__actions">
                            <a href="{{ route('admin.users.show', $u) }}" class="admin-btn admin-btn--ghost">Xem</a>
                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggleBlock', $u) }}">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn--{{ $u->status === 'active' ? 'warning' : 'success' }}">
                                        {{ $u->status === 'active' ? 'Khoá' : 'Mở khoá' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty"><p>Không tìm thấy tài khoản nào.</p></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="admin-pagination">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
