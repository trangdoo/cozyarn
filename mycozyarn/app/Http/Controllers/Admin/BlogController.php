<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    private const PAGE_SIZE = 12;

    public function index(Request $request)
    {
        $posts = $this->allPosts();

        $q        = trim((string) $request->query('q', ''));
        $category = $request->query('category', 'all');
        $tag      = trim((string) $request->query('tag', ''));
        $status   = $request->query('status', 'all'); // all | published | scheduled

        $now = now()->toDateTimeString();

        $filtered = array_filter($posts, function ($p) use ($q, $category, $tag, $status, $now) {
            if ($q !== '') {
                $hay = mb_strtolower(($p['title'] ?? '') . ' ' . ($p['excerpt'] ?? ''));
                foreach ($p['sections'] ?? [] as $s) {
                    $hay .= ' ' . mb_strtolower(strip_tags(($s['heading'] ?? '') . ' ' . ($s['body'] ?? '')));
                }
                if (!str_contains($hay, mb_strtolower($q))) return false;
            }
            if ($category !== 'all' && ($p['category'] ?? '') !== $category) return false;
            if ($tag !== '' && !\in_array($tag, $p['tags'] ?? [], true)) return false;
            if ($status === 'scheduled' && ($p['publish_at'] ?? $p['date'] ?? '') <= $now) return false;
            if ($status === 'published' && ($p['publish_at'] ?? $p['date'] ?? '') > $now) return false;
            return true;
        });

        $page  = max(1, (int) $request->query('page', 1));
        $items = \array_slice($filtered, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE);

        $paginator = new LengthAwarePaginator(
            $items,
            \count($filtered),
            self::PAGE_SIZE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Tập hợp tags để filter
        $allTags = [];
        foreach ($posts as $p) {
            foreach ($p['tags'] ?? [] as $t) $allTags[$t] = ($allTags[$t] ?? 0) + 1;
        }
        arsort($allTags);

        return view('admin.blog.index', [
            'posts'      => $paginator,
            'categories' => (require resource_path('blog.php'))['categories'],
            'tags'       => array_slice($allTags, 0, 20, true),
            'filter'     => compact('q', 'category', 'tag', 'status'),
            'stats'      => [
                'total'     => \count($posts),
                'published' => \count(array_filter($posts, fn($p) => ($p['publish_at'] ?? $p['date'] ?? '') <= $now)),
                'scheduled' => \count(array_filter($posts, fn($p) => ($p['publish_at'] ?? $p['date'] ?? '') > $now)),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $post = collect($this->allPosts())->firstWhere('slug', $slug);
        abort_unless($post, 404);

        $categories = (require resource_path('blog.php'))['categories'];
        $category   = $categories[$post['category']] ?? null;

        return view('admin.blog.show', [
            'post'     => $post,
            'category' => $category,
            'isScheduled' => ($post['publish_at'] ?? $post['date'] ?? '') > now()->toDateTimeString(),
        ]);
    }

    public function create()
    {
        return view('admin.blog.form', [
            'post'       => null,
            'categories' => (require resource_path('blog.php'))['categories'],
        ]);
    }

    public function edit(string $slug)
    {
        $post = collect($this->allPosts())->firstWhere('slug', $slug);
        abort_unless($post, 404);
        return view('admin.blog.form', [
            'post'       => $post,
            'categories' => (require resource_path('blog.php'))['categories'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug']       = Str::slug($data['title']) . '-' . substr((string) Str::uuid(), 0, 6);
        $data['date']       ??= now()->toDateString();
        $data['author']     ??= auth()->user()->name ?? 'Admin';
        $data['sections']   = [['heading' => '', 'body' => $data['body'] ?? "<p>{$data['excerpt']}</p>"]];
        $data['tags']       = $this->parseTags($data['tags_raw'] ?? '');
        $data['publish_at'] ??= now()->toDateTimeString();
        unset($data['tags_raw'], $data['body']);

        $added = session('admin_blogs_added', []);
        $added[$data['slug']] = $data;
        session(['admin_blogs_added' => $added]);

        $isScheduled = $data['publish_at'] > now()->toDateTimeString();
        $msg = $isScheduled ? 'Đã lên lịch đăng bài.' : 'Đã đăng bài viết.';
        return redirect()->route('admin.blog.index')->with('cart_flash', $msg);
    }

    public function update(Request $request, string $slug)
    {
        $data = $this->validateData($request);
        $data['tags'] = $this->parseTags($data['tags_raw'] ?? '');
        if (isset($data['body'])) {
            $data['sections'] = [['heading' => '', 'body' => $data['body']]];
        }
        unset($data['tags_raw'], $data['body']);

        $edited = session('admin_blogs_edited', []);
        $edited[$slug] = [...($edited[$slug] ?? []), ...$data];
        session(['admin_blogs_edited' => $edited]);

        return redirect()->route('admin.blog.index')->with('cart_flash', 'Đã cập nhật bài viết.');
    }

    public function destroy(string $slug)
    {
        $this->markDeleted($slug);
        return back()->with('cart_flash', 'Đã xoá bài viết.');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'slugs'   => 'required|array|min:1',
            'slugs.*' => 'string',
        ]);
        $n = 0;
        foreach ($data['slugs'] as $slug) {
            $this->markDeleted($slug);
            $n++;
        }
        return back()->with('cart_flash', "Đã xoá {$n} bài viết.");
    }

    public function toggleFeatured(string $slug)
    {
        $featured = session('admin_blogs_featured', []);
        if (\in_array($slug, $featured, true)) {
            $featured = array_values(array_filter($featured, fn($s) => $s !== $slug));
            $msg = 'Đã bỏ nổi bật.';
        } else {
            $featured[] = $slug;
            $msg = 'Đã đặt nổi bật.';
        }
        session(['admin_blogs_featured' => $featured]);
        return back()->with('cart_flash', $msg);
    }

    /* ═══════════════════ helpers ═══════════════════ */

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'      => 'required|string|max:200',
            'excerpt'    => 'required|string|max:500',
            'body'       => 'nullable|string',
            'category'   => 'required|string|max:80',
            'cover'      => 'nullable|string|max:300',
            'author'     => 'nullable|string|max:100',
            'read_time'  => 'required|integer|min:1|max:60',
            'date'       => 'nullable|date',
            'publish_at' => 'nullable|date',
            'featured'   => 'nullable|boolean',
            'tags_raw'   => 'nullable|string|max:300',
        ]);
    }

    private function parseTags(string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private function markDeleted(string $slug): void
    {
        $deleted = session('admin_blogs_deleted', []);
        $deleted[] = $slug;
        session(['admin_blogs_deleted' => array_unique($deleted)]);

        $added = session('admin_blogs_added', []);
        unset($added[$slug]);
        session(['admin_blogs_added' => $added]);
    }

    private function allPosts(): array
    {
        $blog    = require resource_path('blog.php');
        $added   = session('admin_blogs_added', []);
        $edited  = session('admin_blogs_edited', []);
        $deleted = session('admin_blogs_deleted', []);

        $result = [];
        foreach ($blog['posts'] as $p) {
            if (\in_array($p['slug'], $deleted, true)) continue;
            $p['publish_at'] ??= ($p['date'] ?? now()->toDateTimeString()) . ' 00:00:00';
            if (isset($edited[$p['slug']])) $p = [...$p, ...$edited[$p['slug']]];
            $result[] = $p;
        }
        foreach ($added as $p) {
            $p['publish_at'] ??= now()->toDateTimeString();
            $result[] = $p;
        }

        usort($result, fn($a, $b) => strcmp($b['publish_at'] ?? '', $a['publish_at'] ?? ''));
        return $result;
    }
}
