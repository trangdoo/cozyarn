@extends('layouts.admin')

@section('title', 'Thông báo — CozyYarn')
@section('page_title', 'Thông báo hệ thống')

@php $active = 'notifications'; @endphp

@section('content')
<div class="admin-page">
    <div class="admin-page__head">
        <div>
            <h1>Thông báo</h1>
            <p>{{ $stats['all'] }} thông báo · {{ $stats['promo'] }} khuyến mãi · {{ $stats['order'] }} đơn hàng</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="admin-btn admin-btn--primary">＋ Gửi khuyến mãi</a>
    </div>

    <div class="admin-card">
        <table class="admin-table admin-table--full">
            <thead>
                <tr><th>Loại</th><th>Tiêu đề</th><th>Nội dung</th><th>Thời gian</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                    <tr>
                        <td><span class="admin-badge admin-badge--{{ $n['type'] }}">{{ $n['type'] === 'promo' ? 'Khuyến mãi' : 'Đơn hàng' }}</span></td>
                        <td><strong>{{ $n['title'] }}</strong></td>
                        <td><small>{{ Str::limit($n['content'], 100) }}</small></td>
                        <td>{{ \Carbon\Carbon::parse($n['created_at'])->diffForHumans() }}</td>
                        <td class="admin-table__actions">
                            <form method="POST" action="{{ route('admin.notifications.destroy', $n['id']) }}"
                                  onsubmit="return confirm('Xoá thông báo này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn--danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="admin-empty"><p>Chưa có thông báo nào.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
