<?php

namespace App\Http\Controllers\Admin;

use App\Plugin\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Admin → Hệ thống → Plugin.
 * Liệt kê plugin trong app/Plugins/, bật/tắt từng plugin (lưu vào storage/app/plugins.json).
 */
class PluginController extends Controller
{
    public function index()
    {
        $all     = PluginManager::all();
        $enabled = PluginManager::enabledKeys();

        $rows = [];
        foreach ($all as $key => $plugin) {
            $rows[] = [
                'key'         => $key,
                'name'        => $plugin->name(),
                'description' => $plugin->description(),
                'version'     => $plugin->version(),
                'author'      => $plugin->author(),
                'enabled'     => \in_array($key, $enabled, true),
            ];
        }
        // Sắp xếp: bật trước, sau đó theo tên.
        usort($rows, fn($a, $b) => ($b['enabled'] <=> $a['enabled']) ?: strcmp($a['name'], $b['name']));

        return view('admin.plugins.index', [
            'plugins' => $rows,
        ]);
    }

    public function toggle(Request $request, string $key)
    {
        $plugin = PluginManager::get($key);
        abort_unless($plugin !== null, 404);

        $enabled = \in_array($key, PluginManager::enabledKeys(), true);
        $ok = $enabled ? PluginManager::disable($key) : PluginManager::enable($key);

        if (!$ok) {
            return back()->withErrors(['plugin' => 'Không thể cập nhật trạng thái plugin (kiểm tra quyền ghi storage/app/).']);
        }
        $msg = $enabled
            ? "Đã tắt plugin \"{$plugin->name()}\"."
            : "Đã bật plugin \"{$plugin->name()}\".";
        return back()->with('cart_flash', $msg);
    }
}
