<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Product\BulkActionRequest;
use App\Http\Requests\Admin\Product\ImportProductsRequest;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Product CRUD — đã chuyển hoàn toàn sang DB-backed (Eloquent qua Service).
 * Hỗ trợ: list/filter/sort/paginate, create/edit/update/destroy,
 * bulk delete & duplicate, duplicate đơn lẻ, import & export (CSV/JSON/XML).
 */
class ProductController extends Controller
{
    private const PAGE_SIZE = 12;

    public function __construct(
        private readonly ProductService $products,
        private readonly CategoryService $categories,
    ) {}

    /* ───────────────────────── list ───────────────────────── */

    public function index(Request $request)
    {
        $filters = [
            'q'        => trim((string) $request->query('q', '')),
            'category' => $request->query('category', 'all'),
            'status'   => $request->query('status', 'all'),
            'sort'     => $request->query('sort', 'updated_desc'),
        ];

        return view('admin.products.index', [
            'products'   => $this->products->paginate($filters, self::PAGE_SIZE),
            'categories' => $this->categories->keyedBySlug(),
            'filter'     => $filters,
        ]);
    }

    /* ───────────────────────── show ───────────────────────── */

    public function show(string $category, Product $product)
    {
        $this->ensureCategoryMatches($category, $product);
        $product->loadMissing('category');

        return view('admin.products.show', [
            'product'  => $product,
            'category' => $product->category,
        ]);
    }

    /* ───────────────────────── create / edit ───────────────────────── */

    public function create()
    {
        return view('admin.products.form', [
            'categories' => $this->categories->keyedBySlug(),
            'product'    => null,
        ]);
    }

    public function edit(string $category, Product $product)
    {
        $this->ensureCategoryMatches($category, $product);

        return view('admin.products.form', [
            'categories' => $this->categories->keyedBySlug(),
            'product'    => $product->load('category'),
        ]);
    }

    /* ───────────────────────── write ───────────────────────── */

    public function store(StoreProductRequest $request)
    {
        try {
            $this->products->create($request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('cart_flash', $e->getMessage());
        }

        return redirect()
            ->route('admin.products.index')
            ->with('cart_flash', 'Đã thêm sản phẩm mới.');
    }

    public function update(UpdateProductRequest $request, string $category, Product $product)
    {
        $this->ensureCategoryMatches($category, $product);

        try {
            $this->products->update($product, $request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('cart_flash', $e->getMessage());
        }

        return redirect()
            ->route('admin.products.index')
            ->with('cart_flash', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(string $category, Product $product)
    {
        $this->ensureCategoryMatches($category, $product);
        $this->products->delete($product);

        return back()->with('cart_flash', 'Đã xoá sản phẩm.');
    }

    /* ───────────────────────── bulk ───────────────────────── */

    public function bulkDelete(BulkActionRequest $request)
    {
        $n = $this->products->deleteMany($request->input('ids', []));
        return back()->with('cart_flash', "Đã xoá {$n} sản phẩm.");
    }

    public function duplicate(string $category, Product $product)
    {
        $this->ensureCategoryMatches($category, $product);
        $this->products->duplicate($product);

        return back()->with('cart_flash', 'Đã sao chép sản phẩm.');
    }

    public function duplicateMany(BulkActionRequest $request)
    {
        $n = $this->products->duplicateMany($request->input('ids', []));
        return back()->with('cart_flash', "Đã sao chép {$n} sản phẩm.");
    }

    /* ───────────────────────── export ───────────────────────── */

    public function export(Request $request, string $format)
    {
        abort_unless(\in_array($format, ['csv', 'json', 'xml'], true), 404);

        $filters = [
            'q'        => trim((string) $request->query('q', '')),
            'category' => $request->query('category', 'all'),
            'status'   => $request->query('status', 'all'),
            'sort'     => $request->query('sort', 'updated_desc'),
        ];

        $idsParam = (string) $request->query('ids', '');
        $ids = $idsParam !== '' ? explode(',', $idsParam) : [];

        $rows = $this->products->forExport($filters, $ids)->map(fn (Product $p) => [
            'id'          => $p->slug,
            'name'        => $p->name,
            'category'    => $p->category?->slug ?? '',
            'image'       => $p->thumbnail ?? '',
            'unit'        => $p->unit ?? 'cuộn',
            'quantity'    => (int) $p->stock_quantity,
            'price'       => (int) $p->price,
            'description' => $p->short_desc ?? $p->description ?? '',
            'status'      => $p->status,
            'created_at'  => optional($p->created_at)->toDateTimeString() ?? '',
            'updated_at'  => optional($p->updated_at)->toDateTimeString() ?? '',
        ])->all();

        $filename = 'products-' . now()->format('Ymd-His');

        return match ($format) {
            'csv'  => $this->exportCsv($rows,  $filename),
            'json' => $this->exportJson($rows, $filename),
            'xml'  => $this->exportXml($rows,  $filename),
        };
    }

    /* ───────────────────────── import ───────────────────────── */

    public function importForm()
    {
        return view('admin.products.import');
    }

    public function import(ImportProductsRequest $request)
    {
        $file    = $request->file('file');
        $ext     = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        $rows = match ($ext) {
            'csv', 'txt' => $this->parseCsv($content),
            'json'       => $this->parseJson($content),
            'xml'        => $this->parseXml($content),
            default      => [],
        };

        $n = $this->products->importRows($rows);

        return redirect()
            ->route('admin.products.index')
            ->with('cart_flash', "Đã nhập {$n} sản phẩm từ file .{$ext}");
    }

    /* ───────────────────────── helpers ───────────────────────── */

    /**
     * Đảm bảo {category} trong URL khớp với category của product.
     * Trả 404 nếu không khớp để tránh URL gãy hiển thị nhầm sản phẩm.
     */
    private function ensureCategoryMatches(string $categorySlug, Product $product): void
    {
        $product->loadMissing('category');
        abort_unless($product->category && $product->category->slug === $categorySlug, 404);
    }

    /* ─── export formats ─── */

    private function exportCsv(array $rows, string $filename): StreamedResponse
    {
        $cols = ['id', 'name', 'category', 'image', 'unit', 'quantity', 'price', 'description', 'status', 'created_at', 'updated_at'];
        return Response::streamDownload(function () use ($rows, $cols) {
            $out = fopen('php://output', 'w');
            fprintf($out, \chr(0xEF) . \chr(0xBB) . \chr(0xBF));
            fputcsv($out, $cols);
            foreach ($rows as $r) {
                fputcsv($out, array_map(fn ($c) => $r[$c] ?? '', $cols));
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
