<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Blog — admin tạo/sửa (làm sau). User chỉ xem + tim.
 * Session:
 *   session('blog_likes') = [user_id => [slug, slug, ...]]
 */
class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blog  = require resource_path('blog.php');
        $posts = $blog['posts'];

        // Lọc bỏ bài có publish_at tương lai (admin hẹn giờ đăng)
        $now = now()->toDateTimeString();
        $posts = array_values(array_filter($posts, fn($p) => ($p['publish_at'] ?? $p['date']) <= $now));

        usort($posts, fn($a, $b) => strcmp($b['date'], $a['date']));

        $categorySlug = $request->query('category');
        if ($categorySlug && isset($blog['categories'][$categorySlug])) {
            $posts = array_values(array_filter($posts, fn($p) => $p['category'] === $categorySlug));
        }

        $featured = collect($posts)->firstWhere('featured', true) ?? ($posts[0] ?? null);

        return view('user.blog.index', [
            'posts'          => $posts,
            'featured'       => $featured,
            'categories'     => $blog['categories'],
            'activeCategory' => $categorySlug,
            'myLikes'        => $this->myLikes(),
        ]);
    }

    public function show(string $slug)
    {
        $blog = require resource_path('blog.php');
        $post = collect($blog['posts'])->firstWhere('slug', $slug);
        abort_unless($post, 404);

        $related = collect($blog['posts'])
            ->where('slug', '!=', $slug)
            ->where('category', $post['category'])
            ->take(3)
            ->values()
            ->all();
        if (\count($related) < 3) {
            $extra = collect($blog['posts'])
                ->where('slug', '!=', $slug)
                ->whereNotIn('slug', array_column($related, 'slug'))
                ->take(3 - \count($related))
                ->values()
                ->all();
            $related = [...$related, ...$extra];
        }

        return view('user.blog.show', [
            'post'       => $post,
            'category'   => $blog['categories'][$post['category']] ?? null,
            'related'    => $related,
            'categories' => $blog['categories'],
            'myLikes'    => $this->myLikes(),
            'likeCount'  => $this->likeCountFor($slug),
            'isLiked'    => \in_array($slug, $this->myLikes(), true),
        ]);
    }

    public function toggleLike(string $slug)
    {
        $this->ensurePostExists($slug);
        $userId = Auth::id();

        $all  = session('blog_likes', []);
        $mine = $all[$userId] ?? [];

        $now = \in_array($slug, $mine, true);
        if ($now) {
            $mine = array_values(array_filter($mine, fn($s) => $s !== $slug));
            $msg = 'Đã bỏ tim bài viết.';
        } else {
            $mine[] = $slug;
            $msg = 'Đã thả tim! ♡';
        }
        $all[$userId] = $mine;
        session(['blog_likes' => $all]);

        return back()->with('cart_flash', $msg);
    }

    public function liked()
    {
        $blog    = require resource_path('blog.php');
        $myLikes = $this->myLikes();
        $all     = collect($blog['posts']);

        $posts = [];
        foreach (array_reverse($myLikes) as $slug) {
            $post = $all->firstWhere('slug', $slug);
            if ($post) $posts[] = $post;
        }

        return view('user.blog.liked', [
            'posts'      => $posts,
            'categories' => $blog['categories'],
            'myLikes'    => $myLikes,
        ]);
    }

    /* ═══════════════════════════ helpers ═══════════════════════════ */

    private function myLikes(): array
    {
        if (!Auth::check()) return [];
        $all = session('blog_likes', []);
        return $all[Auth::id()] ?? [];
    }

    /**
     * Số tim mặc định deterministic từ slug + cộng thêm 1 nếu user hiện tại đã tim.
     */
    private function likeCountFor(string $slug): int
    {
        $seed = \crc32("{$slug}|like");
        $base = 18 + $seed % 120;

        if (\in_array($slug, $this->myLikes(), true)) $base += 1;

        return $base;
    }

    private function ensurePostExists(string $slug): void
    {
        $blog = require resource_path('blog.php');
        $found = collect($blog['posts'])->firstWhere('slug', $slug);
        abort_unless($found, 404);
    }
}
