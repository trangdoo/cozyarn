@extends('layouts.admin')

@section('title', 'Plugin — Quản trị')

@php $active = 'plugins'; @endphp

@section('content')
<section class="admin-content__inner" style="padding:24px;max-width:1100px;margin:0 auto">
    <div style="margin-bottom:18px">
        <h1 style="margin:0 0 6px;font-size:24px">Plugin (Tùy biến chức năng)</h1>
        <p style="margin:0;color:#6c5b66;font-size:14px;line-height:1.6">
            Plugin được scan từ <code>app/Plugins/{Tên}/Plugin.php</code>.
            Mỗi plugin kế thừa <code>App\Plugin\Plugin</code> và đăng ký listener qua <code>App\Plugin\Hook</code>.
            Trạng thái bật/tắt lưu trong <code>storage/app/plugins.json</code>.
        </p>
    </div>

    @if($errors->any())
        <div class="cart-alert" style="margin-bottom:14px">
            @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
        </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:14px">
        @forelse($plugins as $p)
            <div class="co-card" style="margin:0;padding:18px;display:flex;gap:16px;align-items:flex-start">
                <div style="width:48px;height:48px;border-radius:12px;background:{{ $p['enabled'] ? '#dcfce7' : '#f3f4f6' }};color:{{ $p['enabled'] ? '#166534' : '#6b7280' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 2v4M15 2v4M3 8h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg>
                </div>

                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <h3 style="margin:0;font-size:17px">{{ $p['name'] }}</h3>
                        <span style="font-size:11px;color:#6b7280;background:#f3f4f6;padding:2px 8px;border-radius:999px">v{{ $p['version'] }}</span>
                        @if($p['enabled'])
                            <span style="font-size:11px;color:#166534;background:#dcfce7;padding:2px 8px;border-radius:999px">● Đang chạy</span>
                        @else
                            <span style="font-size:11px;color:#6b7280;background:#f3f4f6;padding:2px 8px;border-radius:999px">○ Tắt</span>
                        @endif
                    </div>
                    <p style="margin:0 0 6px;color:#6c5b66;font-size:14px;line-height:1.55">{{ $p['description'] }}</p>
                    <small style="color:#9ca3af">key: <code>{{ $p['key'] }}</code> · author: {{ $p['author'] }}</small>
                </div>

                <form method="POST" action="{{ route('admin.plugins.toggle', $p['key']) }}" style="flex-shrink:0">
                    @csrf
                    <button type="submit" class="cart-btn {{ $p['enabled'] ? 'cart-btn--ghost' : 'cart-btn--primary' }}">
                        {{ $p['enabled'] ? 'Tắt' : 'Bật' }}
                    </button>
                </form>
            </div>
        @empty
            <div class="co-card" style="padding:24px;text-align:center;color:#6c5b66">
                Chưa có plugin nào. Tạo <code>app/Plugins/{Tên}/Plugin.php</code> kế thừa <code>App\Plugin\Plugin</code>.
            </div>
        @endforelse
    </div>

    <div class="co-card" style="margin-top:24px;padding:18px">
        <h3 style="margin:0 0 10px;font-size:15px">🔌 Cách viết plugin mới</h3>
        <pre style="margin:0;background:#1a1a2e;color:#e8d5e0;padding:14px;border-radius:8px;font-size:12px;line-height:1.6;overflow-x:auto">
&lt;?php
namespace App\Plugins\MyFeature;
use App\Plugin\{Plugin as BasePlugin, Hook};

class Plugin extends BasePlugin {
    public function key(): string         { return 'my_feature'; }
    public function name(): string        { return 'My Feature'; }
    public function description(): string { return 'Mô tả ngắn'; }

    public function boot(): void {
        Hook::listen('home.top', fn() =&gt; '&lt;div&gt;Hello&lt;/div&gt;');
        Hook::listen('checkout.total', fn(int $t, array $ctx = []) =&gt; $t - 1000);
    }
}</pre>
    </div>
</section>
@endsection
