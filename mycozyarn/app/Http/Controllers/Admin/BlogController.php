<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        return view('admin.blog.index', [
            'posts'      => $this->allPosts(),
            'categories' => (require resource_path('blog.php'))['categories'],
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
        $data['slug']      = Str::slug($data['title']) . '-' . substr((string) Str::uuid(), 0, 6);
        $data['date']      = $data['date'] ?? now()->toDateString();
        $data['author']    = $data['author'] ?? (auth()->user()->name ?? 'Admin');
        $data['sections']  = [['heading' => 'Nội dung', 'body' => "<p>{$data['excerpt']}</p>"]];
        $data['tags']      = array_values(array_filter(array_map('trim', explode(',', $data['tags_raw'] ?? ''))));
        unset($data['tags_raw']);

        $added = session('admin_blogs_added', []);
        $added[$data['slug']] = $data;
        session(['admin_blogs_added' => $added]);

        return redirect()->route('admin.blog.index')->with('cart_flash', 'Đã đăng bài viết.');
    }

    public function update(Request $request, string $slug)
    {
        $data = $this->validateData($request);
        $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags_raw'] ?? ''))));
        unset($data['tags_raw']);

        $edited = session('admin_blogs_edited', []);
        $edited[$slug] = $data;
        session(['admin_blogs_edited' => $edited]);

        return redirect()->route('admin.blog.index')->with('cart_flash', 'Đã cập nhật bài viết.');
    }

    public function destroy(string $slug)
    {
        $deleted = session('admin_blogs_deleted', []);
        $deleted[] = $slug;
        session(['admin_blogs_deleted' => array_unique($deleted)]);

        $added = session('admin_blogs_added', []);
        unset($added[$slug]);
        session(['admin_blogs_added' => $added]);

        return back()->with('cart_flash', 'Đã xoá bài viết.');
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

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'     => 'required|string|max:200',
            'excerpt'   => 'required|string|max:500',
            'category'  => 'required|string|max:80',
            'cover'     => 'nullable|string|max:300',
            'author'    => 'nullable|string|max:100',
            'read_time' => 'required|integer|min:1|max:60',
            'date'      => 'nullable|date',
            'featured'  => 'nullable|boolean',
            'tags_raw'  => 'nullable|string|max:300',
        ]);
    }

    private function allPosts(): array
    {
        $blog    = require resource_path('blog.php');
        $posts   = $blog['posts'];
        $added   = session('admin_blogs_added', []);
        $edited  = session('admin_blogs_edited', []);
        $deleted = session('admin_blogs_deleted', []);

        $result = [];
        foreach ($posts as $p) {
            if (\in_array($p['slug'], $deleted, true)) continue;
            if (isset($edited[$p['slug']])) $p = array_merge($p, $edited[$p['slug']]);
            $result[] = $p;
        }
        foreach ($added as $p) $result[] = $p;

        usort($result, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
        return $result;
    }
}
