<?php
/**
 * Image uploads: validation, re-encoding, responsive variants (WebP + JPEG/PNG),
 * tiny blurred previews, and 360° panorama files.
 */
final class Media
{
    public const WIDTHS = [480, 960, 1600, 2400];
    public const MAX_EDGE = 2400;
    private static array $rows = [];

    public static function remember(array $row): void
    {
        self::$rows[(int)$row['id']] = $row;
    }

    public static function get(int $id): ?array
    {
        if (!isset(self::$rows[$id])) {
            $row = DB::one('SELECT * FROM media WHERE id = ?', [$id]);
            if (!$row) {
                return null;
            }
            self::$rows[$id] = $row;
        }
        return self::$rows[$id];
    }

    public static function hasWebp(): bool
    {
        return function_exists('imagewebp') && (gd_info()['WebP Support'] ?? false);
    }

    /** Bytes we may use for GD, estimated from memory_limit. */
    private static function memoryBudget(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1' || $limit === false) {
            return 1 << 30;
        }
        $n = (int)$limit;
        $u = strtolower(substr(trim($limit), -1));
        $bytes = match ($u) {
            'g' => $n * 1024 * 1024 * 1024,
            'm' => $n * 1024 * 1024,
            'k' => $n * 1024,
            default => $n,
        };
        return max(0, $bytes - memory_get_usage(true) - 8 * 1024 * 1024);
    }

    private static function fits(int $w, int $h, int $copies = 2): bool
    {
        return ($w * $h * 5 * $copies) < self::memoryBudget();
    }

    /** Check that a file really is an image we accept. Returns [width, height, type] or throws. */
    public static function inspect(string $path): array
    {
        if (!is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('empty_file');
        }
        $info = @getimagesize($path);
        if (!$info) {
            throw new RuntimeException('not_image');
        }
        $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
        if (!in_array($info[2], $allowed, true)) {
            throw new RuntimeException('bad_type');
        }
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($f, $path);
            unset($f);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                throw new RuntimeException('bad_type');
            }
        }
        if ($info[0] < 16 || $info[1] < 16 || $info[0] > 16384 || $info[1] > 16384) {
            throw new RuntimeException('bad_size');
        }
        return [$info[0], $info[1], $info[2]];
    }

    private static function load(string $path, int $type)
    {
        $img = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false,
        };
        if (!$img) {
            throw new RuntimeException('decode_failed');
        }
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $o = (int)($exif['Orientation'] ?? 1);
            $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($rot) {
                $r = imagerotate($img, $rot, 0);
                if ($r) {
                    unset($img);
                    $img = $r;
                }
            }
        }
        if (!imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }
        return $img;
    }

    private static function hasAlpha($img, int $type): bool
    {
        if ($type !== IMAGETYPE_PNG && $type !== IMAGETYPE_GIF && $type !== IMAGETYPE_WEBP) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $step = max(1, (int)(min($w, $h) / 40));
        for ($x = 0; $x < $w; $x += $step) {
            for ($y = 0; $y < $h; $y += $step) {
                if (((imagecolorat($img, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function resize($img, int $w, bool $alpha)
    {
        $sw = imagesx($img);
        $sh = imagesy($img);
        if ($w >= $sw) {
            return $img;
        }
        $h = max(1, (int)round($sh * $w / $sw));
        $dst = imagecreatetruecolor($w, $h);
        if ($alpha) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        }
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $w, $h, $sw, $sh);
        return $dst;
    }

    private static function newBase(): string
    {
        $dir = date('Y/m');
        if (!is_dir(UPLOADS . '/' . $dir)) {
            @mkdir(UPLOADS . '/' . $dir, 0755, true);
        }
        return $dir . '/' . bin2hex(random_bytes(6));
    }

    /**
     * Store an uploaded image and create its variants. Returns the new media row.
     */
    public static function storeImage(string $tmpPath, string $originalName = '', array $alt = []): array
    {
        [$w, $h, $type] = self::inspect($tmpPath);
        if (!self::fits($w, $h, 3)) {
            throw new RuntimeException('too_large_for_memory');
        }
        $img = self::load($tmpPath, $type);
        $w = imagesx($img);
        $h = imagesy($img);
        $alpha = self::hasAlpha($img, $type);
        $fallback = $alpha ? 'png' : 'jpg';
        $base = self::newBase();

        // Largest stored size
        if (max($w, $h) > self::MAX_EDGE) {
            $scale = self::MAX_EDGE / max($w, $h);
            $nw = (int)round($w * $scale);
            $r = self::resize($img, $nw, $alpha);
            unset($img);
            $img = $r;
            $w = imagesx($img);
            $h = imagesy($img);
        }

        $widths = [];
        foreach (self::WIDTHS as $vw) {
            if ($vw < $w) {
                $widths[] = $vw;
            }
        }
        $widths[] = $w; // full size
        $widths = array_values(array_unique($widths));
        $formats = self::hasWebp() ? ['webp', $fallback] : [$fallback];

        $size = 0;
        foreach ($widths as $vw) {
            $v = self::resize($img, $vw, $alpha);
            foreach ($formats as $fmt) {
                $file = UPLOADS . '/' . $base . '-' . $vw . '.' . $fmt;
                self::save($v, $file, $fmt);
                $size += (int)@filesize($file);
            }
            if ($v !== $img) {
                unset($v);
            }
        }

        // Blurred preview + average colour
        $tw = 24;
        $th = max(1, (int)round($h * $tw / $w));
        $tiny = imagecreatetruecolor($tw, $th);
        imagecopyresampled($tiny, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
        ob_start();
        imagejpeg($tiny, null, 45);
        $lqip = 'data:image/jpeg;base64,' . base64_encode((string)ob_get_clean());
        $px = imagecreatetruecolor(1, 1);
        imagecopyresampled($px, $tiny, 0, 0, 0, 0, 1, 1, $tw, $th);
        $rgb = imagecolorat($px, 0, 0);
        $color = sprintf('#%02x%02x%02x', ($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255);
        unset($px);
        unset($tiny);
        unset($img);

        $row = [
            'kind' => 'image',
            'file' => $base,
            'ext' => $fallback,
            'mime' => 'image/' . ($fallback === 'jpg' ? 'jpeg' : 'png'),
            'width' => $w,
            'height' => $h,
            'size' => $size,
            'variants' => json_enc(['w' => $widths, 'f' => $formats]),
            'lqip' => $lqip,
            'color' => $color,
            'alt' => json_enc($alt ?: new stdClass()),
            'title' => mb_substr(pathinfo($originalName, PATHINFO_FILENAME), 0, 150),
            'created_at' => DB::now(),
        ];
        $row['id'] = DB::insert('media', $row);
        self::remember($row);
        return $row;
    }

    /**
     * Store a 360° panorama. The admin page resizes in the browser and sends
     * main (<= 8192px wide), mobile (4096px) and thumb (1024px) versions.
     */
    public static function storePano(string $mainTmp, ?string $mobileTmp, ?string $thumbTmp, string $originalName = ''): array
    {
        [$w, $h, $type] = self::inspect($mainTmp);
        $base = 'pano/' . bin2hex(random_bytes(6));
        if (!is_dir(UPLOADS . '/pano')) {
            @mkdir(UPLOADS . '/pano', 0755, true);
        }
        $ext = $type === IMAGETYPE_PNG ? 'png' : ($type === IMAGETYPE_WEBP ? 'webp' : 'jpg');
        $variants = [];
        $size = 0;
        $put = function (string $tmp, string $suffix) use ($base, &$size): string {
            [, , $t] = self::inspect($tmp);
            $e = $t === IMAGETYPE_PNG ? 'png' : ($t === IMAGETYPE_WEBP ? 'webp' : 'jpg');
            $rel = $base . $suffix . '.' . $e;
            if (!@copy($tmp, UPLOADS . '/' . $rel)) {
                throw new RuntimeException('write_failed');
            }
            $size += (int)filesize(UPLOADS . '/' . $rel);
            return $rel;
        };
        $variants['main'] = $put($mainTmp, '');

        $img = null;
        $canGd = self::fits($w, $h, 2);
        if ($mobileTmp) {
            $variants['mobile'] = $put($mobileTmp, '-m');
        } elseif ($w > 4096 && $canGd) {
            $img = $img ?: self::load($mainTmp, $type);
            $m = self::resize($img, 4096, false);
            self::save($m, UPLOADS . '/' . $base . '-m.jpg', 'jpg', 84);
            unset($m);
            $variants['mobile'] = $base . '-m.jpg';
        }
        if ($thumbTmp) {
            $variants['thumb'] = $put($thumbTmp, '-t');
        } elseif ($canGd) {
            $img = $img ?: self::load($mainTmp, $type);
            $t = self::resize($img, 1024, false);
            self::save($t, UPLOADS . '/' . $base . '-t.jpg', 'jpg', 80);
            unset($t);
            $variants['thumb'] = $base . '-t.jpg';
        }
        $color = '#334455';
        $lqip = '';
        if (isset($variants['thumb'])) {
            $timg = self::load(UPLOADS . '/' . $variants['thumb'], self::inspect(UPLOADS . '/' . $variants['thumb'])[2]);
            $tiny = imagecreatetruecolor(32, 16);
            imagecopyresampled($tiny, $timg, 0, 0, 0, 0, 32, 16, imagesx($timg), imagesy($timg));
            ob_start();
            imagejpeg($tiny, null, 45);
            $lqip = 'data:image/jpeg;base64,' . base64_encode((string)ob_get_clean());
            $rgb = imagecolorat($tiny, 16, 8);
            $color = sprintf('#%02x%02x%02x', ($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255);
            unset($tiny);
            unset($timg);
        }
        if ($img) {
            unset($img);
        }
        $row = [
            'kind' => 'pano', 'file' => $base, 'ext' => $ext, 'mime' => 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext),
            'width' => $w, 'height' => $h, 'size' => $size, 'variants' => json_enc($variants), 'lqip' => $lqip,
            'color' => $color, 'alt' => '{}', 'title' => mb_substr(pathinfo($originalName, PATHINFO_FILENAME), 0, 150),
            'created_at' => DB::now(),
        ];
        $row['id'] = DB::insert('media', $row);
        self::remember($row);
        return $row;
    }

    private static function save($img, string $file, string $fmt, int $q = 0): void
    {
        $ok = match ($fmt) {
            'webp' => imagewebp($img, $file, $q ?: 80),
            'png' => imagepng($img, $file, 7),
            default => (function () use ($img, $file, $q) {
                imageinterlace($img, true);
                return imagejpeg($img, $file, $q ?: 82);
            })(),
        };
        if (!$ok) {
            throw new RuntimeException('write_failed');
        }
    }

    public static function variants(array $m): array
    {
        return json_dec($m['variants'] ?? '', []);
    }

    /** URL of the variant closest to (and not smaller than) the requested width. */
    public static function src(array $m, int $want = 960, string $fmt = 'jpg'): string
    {
        $v = self::variants($m);
        if (($m['kind'] ?? '') === 'pano') {
            $key = $want <= 1024 && isset($v['thumb']) ? 'thumb' : ($want <= 4096 && isset($v['mobile']) ? 'mobile' : 'main');
            return upload_url($v[$key] ?? ($v['main'] ?? ''));
        }
        $widths = $v['w'] ?? [(int)$m['width']];
        sort($widths);
        $pick = end($widths);
        foreach ($widths as $w) {
            if ($w >= $want) {
                $pick = $w;
                break;
            }
        }
        $formats = $v['f'] ?? [$m['ext']];
        if (!in_array($fmt, $formats, true)) {
            $fmt = $m['ext'];
        }
        return upload_url($m['file'] . '-' . $pick . '.' . $fmt);
    }

    public static function picture(array $m, array $o): string
    {
        $alt = $o['alt'] ?? tr($m['alt'] ?? '');
        $sizes = $o['sizes'] ?? '100vw';
        $class = $o['class'] ?? '';
        $eager = !empty($o['eager']);
        $max = (int)($o['max'] ?? 99999);
        if (($m['kind'] ?? '') === 'pano') {
            $src = self::src($m, 1024);
            return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . e($alt) . '" width="1024" height="512" loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async" style="background-color:' . e($m['color'] ?: '#333') . '">';
        }
        $v = self::variants($m);
        $widths = array_values(array_filter($v['w'] ?? [(int)$m['width']], fn($w) => $w <= $max)) ?: [min($v['w'] ?? [(int)$m['width']])];
        sort($widths);
        $formats = $v['f'] ?? [$m['ext']];
        $fallback = $m['ext'];
        $set = function (string $fmt) use ($widths, $m) {
            return implode(', ', array_map(fn($w) => upload_url($m['file'] . '-' . $w . '.' . $fmt) . ' ' . $w . 'w', $widths));
        };
        $mid = $widths[0];
        foreach ($widths as $w) {
            if ($w >= 960) {
                $mid = $w;
                break;
            }
            $mid = $w;
        }
        $style = 'background-color:' . ($m['color'] ?: '#ddd');
        if (!empty($m['lqip']) && empty($o['nolqip'])) {
            $style .= ';background-image:url(' . $m['lqip'] . ');background-size:cover;background-position:center';
        }
        $h = '<picture>';
        if (in_array('webp', $formats, true) && $fallback !== 'webp') {
            $h .= '<source type="image/webp" srcset="' . e($set('webp')) . '" sizes="' . e($sizes) . '">';
        }
        $h .= '<img class="' . e($class) . '" src="' . e(upload_url($m['file'] . '-' . $mid . '.' . $fallback)) . '" srcset="' . e($set($fallback)) . '" sizes="' . e($sizes) . '"'
            . ' width="' . (int)$m['width'] . '" height="' . (int)$m['height'] . '" alt="' . e($alt) . '"'
            . ' loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async"' . ($eager ? ' fetchpriority="high"' : '')
            . ' style="' . e($style) . '">';
        return $h . '</picture>';
    }

    /** Delete media files and the row. */
    public static function delete(int $id): bool
    {
        $m = DB::one('SELECT * FROM media WHERE id = ?', [$id]);
        if (!$m) {
            return false;
        }
        $dir = UPLOADS . '/' . dirname($m['file']);
        $base = basename($m['file']);
        foreach (glob($dir . '/' . $base . '*') ?: [] as $f) {
            if (is_file($f) && str_starts_with(basename($f), $base)) {
                @unlink($f);
            }
        }
        DB::delete('media', 'id = ?', [$id]);
        unset(self::$rows[$id]);
        return true;
    }

    public static function uploadError(string $code): string
    {
        $map = [
            'empty_file' => __('admin.upload_err_empty'),
            'not_image' => __('admin.upload_err_type'),
            'bad_type' => __('admin.upload_err_type'),
            'bad_size' => __('admin.upload_err_dims'),
            'too_large_for_memory' => __('admin.upload_err_memory'),
            'decode_failed' => __('admin.upload_err_type'),
            'write_failed' => __('admin.upload_err_write'),
        ];
        return $map[$code] ?? $code;
    }
}
