<?php
/**
 * Full-page HTML cache for public pages. Cleared whenever anything is saved in the admin.
 */
final class Cache
{
    private const TTL = 86400;

    private static function dir(): string
    {
        return STORAGE . '/cache/pages';
    }

    public static function key(string $uri): string
    {
        return self::dir() . '/' . sha1($uri) . '.html';
    }

    public static function enabled(): bool
    {
        return (bool)setting('system.cache', true) && !cfg('debug');
    }

    public static function serve(string $uri): bool
    {
        $f = self::key($uri);
        if (is_file($f) && (time() - filemtime($f)) < self::TTL) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Cache: HIT');
            header('Cache-Control: public, max-age=0, must-revalidate');
            $etag = '"' . md5_file($f) . '"';
            header('ETag: ' . $etag);
            if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
                http_response_code(304);
                return true;
            }
            readfile($f);
            return true;
        }
        return false;
    }

    public static function store(string $uri, string $html): void
    {
        if (!is_dir(self::dir())) {
            @mkdir(self::dir(), 0755, true);
        }
        $tmp = self::key($uri) . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, $html) !== false) {
            @rename($tmp, self::key($uri));
        }
    }

    public static function flush(): int
    {
        $n = 0;
        foreach (glob(self::dir() . '/*.html') ?: [] as $f) {
            if (@unlink($f)) {
                $n++;
            }
        }
        return $n;
    }

    public static function size(): array
    {
        $files = glob(self::dir() . '/*.html') ?: [];
        $bytes = 0;
        foreach ($files as $f) {
            $bytes += (int)filesize($f);
        }
        return ['files' => count($files), 'bytes' => $bytes];
    }
}
