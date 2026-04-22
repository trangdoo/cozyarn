<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin products — full-feature UI demo.
 * Đọc shop.php + overlay session:
 *   session('admin_products_added')   — records mới
 *   session('admin_products_edited')  — {cat::slug} => overlay fields
 *   session('admin_products_deleted') — list "cat::slug"
 * Khi migrate sang DB: đổi allProducts()/save ops sang Eloquent.
 */
class ProductController extends Controller
{
    private const PAGE_SIZE = 12;

    /* ═══════════════════════════════════════ list + filters + pagination ═══════════════════════════════════════ */

    public function index(Request $request)
    {
        $all = $this->allProducts();

        $q        = trim((string) $request->query('q', ''));
        $category = $request->query('category', 'all');
        $status   = $request->query('status', 'all');
        $sort     = $request->query('sort', 'updated_desc');

        $filtered = array_filter($all, function ($p) use ($q, $category, $status) {
            if ($q !== '') {
                $needle = mb_strtolower($q);
                $hay = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['slug'] ?? '') . ' ' . ($p['shortDesc'] ?? '') . ' ' . ($p['desc'] ?? ''));
                if (!str_contains($hay, $needle)) return false;
            }
            if ($category !== 'all' && ($p['category_slug'] ?? '') !== $category) return false;
            if ($status !== 'all' && ($p['status'] ?? 'active') !== $status) return false;
            return true;
        });

        // Sort
        usort($filtered, match ($sort) {
            'name_asc'     => fn($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''),
            'name_desc'    => fn($a, $b) => strcasecmp($b['name'] ?? '', $a['name'] ?? ''),
            'price_asc'    => fn($a, $b) => ($a['price'] ?? 0) <=> ($b['price'] ?? 0),
            'price_desc'   => fn($a, $b) => ($b['price'] ?? 0) <=> ($a['price'] ?? 0),
            'created_desc' => fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''),
            default        => fn($a, $b) => strcmp($b['updated_at'] ?? $b['created_at'] ?? '', $a['updated_at'] ?? $a['created_at'] ?? ''),
        });

        $page = max(1, (int) $request->query('page', 1));
        $items = \array_slice($filtered, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE);

        $paginator = new LengthAwarePaginator(
            $items,
            \count($filtered),
            self::PAGE_SIZE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $shop = require resource_path('shop.php');

        return view('admin.products.index', [
            'products'   => $paginator,
            'categories' => $shop['categories'],
            'filter'     => compact('q', 'category', 'status', 'sort'),
        ]);
    }

    /* ═══════════════════════════════════════ show / detail ═══════════════════════════════════════ */

    public function show(string $category, string $slug)
    {
        $product = $this->findOrFail($category, $slug);
        $shop = require resource_path('shop.php');
        return view('admin.products.show', [
            'product'  => $product,
            'category' => $shop['categories'][$category] ?? ['name' => $category, 'slug' => $category],
        ]);
    }

    /* ═══════════════════════════════════════ create / edit / update / delete ═══════════════════════════════════════ */

    public function create()
    {
        $shop = require resource_path('shop.php');
        return view('admin.products.form', ['categories' => $shop['categories'], 'product' => null]);
    }

    public function edit(string $category, string $slug)
    {
        $product = $this->findOrFail($category, $slug);
        $shop = require resource_path('shop.php');
        return view('admin.products.form', ['categories' => $shop['categories'], 'product' => $product]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug']       = Str::slug($data['name']) . '-' . substr((string) Str::uuid(), 0, 6);
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        $added = session('admin_products_added', []);
        $added[] = $data;
        session(['admin_products_added' => $added]);

        return redirect()->route('admin.products.index')->with('cart_flash', 'Đã thêm sản phẩm mới.');
    }

    public function update(Request $request, string $category, string $slug)
    {
        $this->findOrFail($category, $slug);
        $data = $this->validateData($request);
        $data['updated_at'] = now()->toDateTimeString();

        $edited = session('admin_products_edited', []);
        $edited["{$category}::{$slug}"] = $data;
        session(['admin_products_edited' => $edited]);

        return redirect()->route('admin.products.index')->with('cart_flash', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(string $category, string $slug)
    {
        $this->markDeleted($category, $slug);
        return back()->with('cart_flash', 'Đã xoá sản phẩm.');
    }

    /* ═══════════════════════════════════════ bulk delete ═══════════════════════════════════════ */

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'string',
        ]);

        $n = 0;
        foreach ($data['ids'] as $id) {
            [$cat, $slug] = array_pad(explode('::', $id, 2), 2, null);
            if (!$cat || !$slug) continue;
            $this->markDeleted($cat, $slug);
            $n++;
        }

        return back()->with('cart_flash', "Đã xoá {$n} sản phẩm.");
    }

    /* ═══════════════════════════════════════ duplicate ═══════════════════════════════════════ */

    public function duplicate(string $category, string $slug)
    {
        $src = $this->findOrFail($category, $slug);
        $this->cloneProduct($src);
        return back()->with('cart_flash', 'Đã sao chép sản phẩm.');
    }

    public function duplicateMany(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'string',
        ]);

        $n = 0;
        foreach ($data['ids'] as $id) {
            [$cat, $slug] = array_pad(explode('::', $id, 2), 2, null);
            if (!$cat || !$slug) continue;
            $src = collect($this->allProducts())->first(fn($p) => ($p['category_slug'] ?? '') === $cat && ($p['slug'] ?? '') === $slug);
            if (!$src) continue;
            $this->cloneProduct($src);
            $n++;
        }

        return back()->with('cart_flash', "Đã sao chép {$n} sản phẩm.");
    }

    /* ═══════════════════════════════════════ export ═══════════════════════════════════════ */

    public function export(Request $request, string $format)
    {
        abort_unless(\in_array($format, ['csv', 'json', 'xml'], true), 404);

        $products = $this->allProducts();

        // Nếu có ids=... → chỉ export selection
        $ids = $request->query('ids');
        if ($ids) {
            $keys = explode(',', $ids);
            $products = array_values(array_filter($products, fn($p) =>
                \in_array(($p['category_slug'] ?? '') . '::' . ($p['slug'] ?? ''), $keys, true)
            ));
        }

        $rows = array_map(fn($p) => [
            'id'          => $p['slug'] ?? '',
            'name'        => $p['name'] ?? '',
            'category'    => $p['category_slug'] ?? '',
            'image'       => $p['image'] ?? '',
            'unit'        => $p['unit'] ?? 'cuộn',
            'quantity'    => (int) ($p['quantity'] ?? array_sum(array_column($p['variants'] ?? [], 'stock'))),
            'price'       => (int) ($p['price'] ?? 0),
            'description' => $p['shortDesc'] ?? $p['desc'] ?? '',
            'status'      => $p['status'] ?? 'active',
            'created_at'  => $p['created_at'] ?? '',
            'updated_at'  => $p['updated_at'] ?? '',
        ], $products);

        $filename = 'products-' . now()->format('Ymd-His');

        return match ($format) {
            'csv'  => $this->exportCsv($rows,  $filename),
            'json' => $this->exportJson($rows, $filename),
            'xml'  => $this->exportXml($rows,  $filename),
        };
    }

    /* ═══════════════════════════════════════ import ═══════════════════════════════════════ */

    public function importForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:csv,txt,json,xml',
        ]);

        $file    = $request->file('file');
        $ext     = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        $rows = match ($ext) {
            'csv', 'txt' => $this->parseCsv($content),
            'json'       => $this->parseJson($content),
            'xml'        => $this->parseXml($content),
            default      => [],
        };

        $added = session('admin_products_added', []);
        $n = 0;
        foreach ($rows as $r) {
            if (empty($r['name'])) continue;
            $added[] = [
                'slug'          => $r['id'] ?: Str::slug($r['name']) . '-' . substr((string) Str::uuid(), 0, 6),
                'name'          => $r['name'],
                'category_slug' => $r['category'] ?? 'len-soi',
                'image'         => $r['image'] ?? '/images/1.jpg',
                'unit'          => $r['unit'] ?? 'cuộn',
                'quantity'      => (int) ($r['quantity'] ?? 0),
                'price'         => (int) ($r['price'] ?? 0),
                'shortDesc'     => $r['description'] ?? '',
                'status'        => $r['status'] ?? 'active',
                'created_at'    => now()->toDateTimeString(),
                'updated_at'    => now()->toDateTimeString(),
            ];
            $n++;
        }
        session(['admin_products_added' => $added]);

        return redirect()->route('admin.products.index')->with('cart_flash', "Đã nhập {$n} sản phẩm từ file .{$ext}");
    }

    /* ═══════════════════════════════════════ helpers ═══════════════════════════════════════ */

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'          => 'required|string|max:200',
            'category_slug' => 'required|string|max:80',
            'shortDesc'     => 'required|string|max:500',
            'desc'          => 'nullable|string|max:3000',
            'price'         => 'required|integer|min:0',
            'oldPrice'      => 'nullable|integer|min:0',
            'quantity'      => 'required|integer|min:0',
            'unit'          => 'required|string|max:30',
            'image'         => 'nullable|string|max:300',
            'tag'           => 'nullable|string|max:30',
            'status'        => 'required|in:active,inactive',
        ]);
    }

    private function findOrFail(string $category, string $slug): array
    {
        $product = collect($this->allProducts())->first(fn($p) =>
            ($p['category_slug'] ?? '') === $category && ($p['slug'] ?? '') === $slug
        );
        abort_unless($product, 404);
        return $product;
    }

    private function markDeleted(string $category, string $slug): void
    {
        $deleted = session('admin_products_deleted', []);
        $deleted[] = "{$category}::{$slug}";
        session(['admin_products_deleted' => array_unique($deleted)]);

        // Cũng xoá khỏi added nếu là record mới tự thêm
        $added = session('admin_products_added', []);
        $added = array_values(array_filter($added, fn($p) =>
            !(($p['category_slug'] ?? '') === $category && ($p['slug'] ?? '') === $slug)
        ));
        session(['admin_products_added' => $added]);
    }

    private function cloneProduct(array $src): void
    {
        $clone = $src;
        $clone['slug']       = Str::slug($src['name']) . '-copy-' . substr((string) Str::uuid(), 0, 5);
        $clone['name']       = $src['name'] . ' (Bản sao)';
        $clone['created_at'] = now()->toDateTimeString();
        $clone['updated_at'] = now()->toDateTimeString();

        $added = session('admin_products_added', []);
        $added[] = $clone;
        session(['admin_products_added' => $added]);
    }

    private function allProducts(): array
    {
        $shop    = require resource_path('shop.php');
        $deleted = session('admin_products_deleted', []);
        $edited  = session('admin_products_edited', []);
        $added   = session('admin_products_added', []);

        $result = [];
        foreach ($shop['products'] as $catSlug => $list) {
            foreach ($list as $p) {
                $key = "{$catSlug}::{$p['slug']}";
                if (\in_array($key, $deleted, true)) continue;
                $p['category_slug'] = $catSlug;
                $p['quantity']   ??= array_sum(array_column($p['variants'] ?? [], 'stock'));
                $p['unit']       ??= 'cuộn';
                $p['created_at'] ??= '2026-01-01 00:00:00';
                $p['updated_at'] ??= '2026-01-01 00:00:00';
                if (isset($edited[$key])) $p = [...$p, ...$edited[$key]];
                $result[] = $p;
            }
        }
        foreach ($added as $p) {
            if (!isset($p['category_slug'])) continue;
            $p['quantity']   ??= 0;
            $p['unit']       ??= 'cuộn';
            $p['created_at'] ??= now()->toDateTimeString();
            $p['updated_at'] ??= now()->toDateTimeString();
            $result[] = $p;
        }
        return $result;
    }

    /* ─── export formats ─── */

    private function exportCsv(array $rows, string $filename): StreamedResponse
    {
        $cols = ['id', 'name', 'category', 'image', 'unit', 'quantity', 'price', 'description', 'status', 'created_at', 'updated_at'];
        return Response::streamDownload(function () use ($rows, $cols) {
            $out = fopen('php://output', 'w');
            // BOM để Excel đọc Unicode
            fprintf($out, \chr(0xEF) . \chr(0xBB) . \chr(0xBF));
            fputcsv($out, $cols);
            foreach ($rows as $r) {
                fputcsv($out, array_map(fn($c) => $r[$c] ?? '', $cols));
            }
            fclose($out);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportJson(array $rows, string $filename)
    {
        return Response::make(
            json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            200,
            [
                'Content-Type'        => 'application/json; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
            ]
        );
    }

    private function exportXml(array $rows, string $filename)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><products/>');
        foreach ($rows as $r) {
            $node = $xml->addChild('product');
            foreach ($r as $k => $v) {
                $child = $node->addChild($k);
                if ($child !== null) $child[0] = (string) $v;
            }
        }
        return Response::make(
            $xml->asXML(),
            200,
            [
                'Content-Type'        => 'application/xml; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}.xml\"",
            ]
        );
    }

    /* ─── import parsers ─── */

    private function parseCsv(string $content): array
    {
        // Strip BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = preg_split('/\r\n|\n|\r/', trim($content));
        if (\count($lines) < 2) return [];

        $header = str_getcsv(array_shift($lines));
        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $values = str_getcsv($line);
            $row = [];
            foreach ($header as $i => $col) {
                $row[$col] = $values[$i] ?? '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function parseJson(string $content): array
    {
        $data = json_decode($content, true);
        return \is_array($data) ? array_values($data) : [];
    }

    private function parseXml(string $content): array
    {
        try {
            $xml = new \SimpleXMLElement($content);
        } catch (\Throwable) {
            return [];
        }
        $rows = [];
        foreach ($xml->children() as $node) {
            $row = [];
            foreach ($node->children() as $child) {
                $row[$child->getName()] = (string) $child;
            }
            $rows[] = $row;
        }
        return $rows;
    }
}
