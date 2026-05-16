<?php

namespace App\Http\Middleware;

use App\Plugin\Hook;
use App\Support\ThemeManager;
use Closure;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware xử lý 2 việc trước/sau request:
 *
 *  1. PRE  : đọc theme active từ storage và gọi Theme::set() — view-finder của
 *            igaster/laravel-theme từ đây resolve view/asset theo theme tương ứng.
 *
 *  2. POST : nếu response là HTML, INJECT trực tiếp vào markup:
 *              • <link rel="stylesheet" href="{skin.css}"> trước </head>
 *              • Plugin hook "site.body_start" / "site.body_end" sau/trước thân
 *                body — giúp plugin (vd: WelcomeBanner) hoạt động ngay cả với
 *                các view standalone không extend layout (vd: user/home/index).
 *
 *  Cách injection-by-middleware này giải quyết bug "đổi skin không thấy gì"
 *  do nhiều view legacy (home, about, shop, product) tự viết <html><head>
 *  không qua layouts/public.
 */
class ApplyTheme
{
    /** URL path prefix sẽ bypass cả theme switch lẫn HTML injection. */
    private const SKIP_PREFIXES = ['webhook/', 'api/', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        // Bypass: webhook (SePay) / health check / API — không phải HTML user-facing.
        $path = trim($request->path(), '/');
        foreach (self::SKIP_PREFIXES as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        // 1) Set theme — Theme::set() đăng ký view path + asset path tương ứng.
        try {
            Theme::set(ThemeManager::active());
        } catch (\Throwable) {
            // Bỏ qua: framework sẽ fallback view path mặc định.
        }

        /** @var Response $response */
        $response = $next($request);

        // 2) Chỉ inject khi response là HTML thường (không inject vào JSON/file/redirect/...).
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && !str_contains($contentType, 'text/html')) {
            return $response;
        }
        // Không inject vào response không có content (vd: 204, 304).
        if (! method_exists($response, 'getContent')) {
            return $response;
        }
        $html = (string) $response->getContent();
        if ($html === '' || !str_contains($html, '</head>')) {
            return $response;
        }

        $html = $this->injectSkinLink($html);
        $html = $this->injectHookOutputs($html);

        $response->setContent($html);
        return $response;
    }

    private function injectSkinLink(string $html): string
    {
        $url = ThemeManager::skinUrl();
        if (!$url) return $html;
        $tag = sprintf(
            "    <link rel=\"stylesheet\" data-theme-skin=\"%s\" href=\"%s\">\n",
            e(ThemeManager::active()),
            e($url),
        );
        // Inject trước </head> đầu tiên — case-insensitive, chỉ 1 lần.
        $out = preg_replace('#</head>#i', $tag . '</head>', $html, 1);
        return $out ?? $html;
    }

    private function injectHookOutputs(string $html): string
    {
        // home.top: chèn ngay sau <body ...> mở (sau body, trước nội dung)
        $topOut = Hook::render('site.body_start') . Hook::render('home.top');
        if ($topOut !== '') {
            $html = preg_replace(
                '#(<body\b[^>]*>)#i',
                '$1' . $topOut,
                $html,
                1,
            ) ?? $html;
        }
        // site.body_end: chèn trước </body>
        $endOut = Hook::render('site.body_end');
        if ($endOut !== '') {
            $html = preg_replace('#</body>#i', $endOut . '</body>', $html, 1) ?? $html;
        }
        return $html;
    }
}
