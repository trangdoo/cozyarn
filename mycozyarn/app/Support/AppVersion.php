<?php

namespace App\Support;

/**
 * Helper đọc phiên bản app từ file VERSION + commit hash từ .git.
 * Hiển thị ở footer admin / trang about — phục vụ tiêu chí "Quản lý phiên bản" trong BCCĐ.
 */
class AppVersion
{
    private static ?string $cachedVersion = null;
    private static ?string $cachedCommit  = null;

    public static function full(): string
    {
        return self::version() . ' (build ' . self::commit() . ')';
    }

    public static function version(): string
    {
        if (self::$cachedVersion !== null) return self::$cachedVersion;
        $path = base_path('VERSION');
        if (is_file($path)) {
            $v = trim((string) @file_get_contents($path));
            if ($v !== '') return self::$cachedVersion = $v;
        }
        return self::$cachedVersion = '0.0.0';
    }

    /** 7-char git short hash; "no-git" nếu không có .git hoặc đọc lỗi. */
    public static function commit(): string
    {
        if (self::$cachedCommit !== null) return self::$cachedCommit;

        // Tìm .git ở project root, fallback lên 1 cấp (case khi Laravel app nằm
        // bên trong 1 mono-repo cha).
        $gitDir = self::findGitDir();
        if ($gitDir === null) return self::$cachedCommit = 'no-git';

        $head = $gitDir . DIRECTORY_SEPARATOR . 'HEAD';
        if (!is_file($head)) return self::$cachedCommit = 'no-git';
        $line = trim((string) @file_get_contents($head));

        // Trường hợp HEAD trỏ trực tiếp tới commit (detached).
        if (preg_match('/^[0-9a-f]{7,}$/i', $line)) {
            return self::$cachedCommit = substr($line, 0, 7);
        }
        // Trường hợp "ref: refs/heads/main".
        if (preg_match('/^ref:\s*(.+)$/', $line, $m)) {
            $refFile = $gitDir . DIRECTORY_SEPARATOR . trim($m[1]);
            if (is_file($refFile)) {
                $sha = trim((string) @file_get_contents($refFile));
                if (preg_match('/^[0-9a-f]{7,}$/i', $sha)) {
                    return self::$cachedCommit = substr($sha, 0, 7);
                }
            }
            // Packed refs fallback.
            $packed = $gitDir . DIRECTORY_SEPARATOR . 'packed-refs';
            if (is_file($packed)) {
                $ref = trim($m[1]);
                foreach (file($packed, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $l) {
                    if (str_starts_with($l, '#') || str_starts_with($l, '^')) continue;
                    [$sha, $r] = array_pad(preg_split('/\s+/', $l, 2), 2, null);
                    if ($r === $ref && preg_match('/^[0-9a-f]{7,}$/i', $sha)) {
                        return self::$cachedCommit = substr($sha, 0, 7);
                    }
                }
            }
        }
        return self::$cachedCommit = 'unknown';
    }

    private static function findGitDir(): ?string
    {
        foreach ([base_path('.git'), dirname(base_path()) . DIRECTORY_SEPARATOR . '.git'] as $dir) {
            if (is_dir($dir)) return $dir;
        }
        return null;
    }
}
