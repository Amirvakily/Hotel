<?php
/**
 * Read-only content queries for the public site.
 */
final class Content
{
    private static array $memo = [];

    private static function memo(string $key, callable $fn)
    {
        if (!array_key_exists($key, self::$memo)) {
            self::$memo[$key] = $fn();
        }
        return self::$memo[$key];
    }

    public static function reset(): void
    {
        self::$memo = [];
    }

    // ---------- Rooms ----------

    public static function rooms(bool $onlyActive = true): array
    {
        return self::memo('rooms' . (int)$onlyActive, function () use ($onlyActive) {
            $rows = DB::all('SELECT * FROM rooms' . ($onlyActive ? ' WHERE active = 1' : '') . ' ORDER BY sort, id');
            $ids = [];
            foreach ($rows as &$r) {
                $r = self::decodeRoom($r);
                $ids[] = $r['cover_id'];
                $ids = array_merge($ids, $r['gallery']);
            }
            media_preload($ids);
            return $rows;
        });
    }

    public static function room(string $slug): ?array
    {
        $r = DB::one('SELECT * FROM rooms WHERE slug = ? AND active = 1', [$slug]);
        return $r ? self::decodeRoom($r) : null;
    }

    public static function decodeRoom(array $r): array
    {
        $r['prices'] = json_dec($r['prices'], []);
        $r['bed_layout'] = json_dec($r['bed_layout'], []);
        $r['amenities'] = array_map('intval', json_dec($r['amenities'], []));
        $r['gallery'] = array_map('intval', json_dec($r['gallery'], []));
        $r['features'] = json_dec($r['features'], []);
        $r['min_guests'] = max(1, (int)($r['min_guests'] ?: 1));
        $r['max_guests'] = max($r['min_guests'], (int)($r['max_guests'] ?: $r['beds'] ?: 1));
        $r['max_extra'] = max(0, (int)($r['max_extra'] ?? 0));
        $r['capacity'] = $r['max_guests'] + $r['max_extra'];
        if (!$r['cover_id'] && $r['gallery']) {
            $r['cover_id'] = $r['gallery'][0];
        }
        return $r;
    }

    /**
     * Price for each guest count from min_guests up to capacity (max_guests + extra people).
     * Extra people cost the full-room price plus the per-person extra charge each. Missing prices are null.
     * @return array{byGuests: array<int, ?int>, extra: array<int, int>, from: ?int, any: bool, extraPrice: ?int}
     */
    public static function roomPrices(array $room): array
    {
        $by = [];
        $extra = [];
        $show = (bool)$room['show_prices'];
        $max = (int)$room['max_guests'];
        $xp = (int)($room['extra_bed_price'] ?? 0) > 0 ? (int)$room['extra_bed_price'] : null;
        for ($g = $room['min_guests']; $g <= $max; $g++) {
            $p = $room['prices'][(string)$g] ?? null;
            $by[$g] = ($show && $p !== null && $p !== '' && (int)$p > 0) ? (int)$p : null;
        }
        for ($g = $max + 1; $g <= $room['capacity']; $g++) {
            $extra[$g] = $g - $max;
            $base = $by[$max] ?? null;
            $by[$g] = ($show && $base && $xp) ? $base + $xp * ($g - $max) : null;
        }
        $vals = array_filter($by);
        return ['byGuests' => $by, 'extra' => $extra, 'from' => $vals ? min($vals) : null, 'any' => (bool)$vals, 'extraPrice' => $show ? $xp : null];
    }

    /** Text shown when a price is missing ('' when the admin chose to hide it). */
    public static function emptyPriceLabel(): string
    {
        return setting('price.empty_mode', 'call') === 'call' ? tr(setting('price.empty_label')) : '';
    }

    public static function maxGuests(): int
    {
        $max = 1;
        foreach (self::rooms() as $r) {
            $max = max($max, (int)$r['capacity']);
        }
        return $max;
    }

    public static function amenities(?string $scope = null): array
    {
        return self::memo('amen' . $scope, function () use ($scope) {
            $rows = DB::all('SELECT * FROM amenities WHERE active = 1 ORDER BY sort, id');
            if ($scope) {
                $rows = array_values(array_filter($rows, fn($a) => $a['scope'] === $scope || $a['scope'] === 'both'));
            }
            $map = [];
            foreach ($rows as $a) {
                $map[(int)$a['id']] = $a;
            }
            return $map;
        });
    }

    // ---------- Gallery ----------

    public static function galleryCategories(): array
    {
        return self::memo('gcat', fn() => DB::all('SELECT * FROM gallery_categories WHERE active = 1 ORDER BY sort, id'));
    }

    public static function galleryItems(?int $categoryId = null, int $limit = 0): array
    {
        $sql = 'SELECT g.*, c.slug AS cat_slug FROM gallery_items g LEFT JOIN gallery_categories c ON c.id = g.category_id WHERE g.active = 1 AND (c.active = 1 OR c.id IS NULL)';
        $p = [];
        if ($categoryId) {
            $sql .= ' AND g.category_id = ?';
            $p[] = $categoryId;
        }
        $sql .= ' ORDER BY c.sort, g.sort, g.id';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        $rows = DB::all($sql, $p);
        media_preload(array_column($rows, 'media_id'));
        return $rows;
    }

    // ---------- Virtual tour ----------

    public static function scenes(): array
    {
        return self::memo('scenes', function () {
            $rows = DB::all('SELECT * FROM scenes WHERE active = 1 ORDER BY sort, id');
            media_preload(array_column($rows, 'media_id'));
            foreach ($rows as &$s) {
                $s['hotspots'] = json_dec($s['hotspots'], []);
            }
            return $rows;
        });
    }

    /** Pannellum "tour" configuration for all scenes. */
    public static function tourConfig(?string $first = null): array
    {
        $scenes = self::scenes();
        $bySlug = [];
        $default = null;
        foreach ($scenes as $s) {
            if ($s['type'] === 'embed') {
                continue;
            }
            $m = media($s['media_id']);
            if (!$m) {
                continue;
            }
            $v = Media::variants($m);
            $hs = [];
            foreach ($s['hotspots'] as $h) {
                $item = [
                    'pitch' => (float)($h['pitch'] ?? 0),
                    'yaw' => (float)($h['yaw'] ?? 0),
                    'type' => ($h['type'] ?? 'info') === 'scene' ? 'scene' : 'info',
                    'text' => tr($h['text'] ?? ''),
                ];
                if ($item['type'] === 'scene') {
                    $item['sceneId'] = (string)($h['target'] ?? '');
                    if (isset($h['targetYaw'])) {
                        $item['targetYaw'] = (float)$h['targetYaw'];
                    }
                }
                $hs[] = $item;
            }
            $conf = [
                'title' => tr($s['title']),
                'type' => 'equirectangular',
                'panorama' => upload_url($v['main'] ?? ''),
                'panoramaMobile' => isset($v['mobile']) ? upload_url($v['mobile']) : null,
                'preview' => isset($v['thumb']) ? upload_url($v['thumb']) : null,
                'thumb' => isset($v['thumb']) ? upload_url($v['thumb']) : null,
                'yaw' => (float)$s['yaw'],
                'pitch' => (float)$s['pitch'],
                'hfov' => (float)($s['hfov'] ?: 100),
                'hotSpots' => $hs,
                'autoRotate' => $s['autorotate'] ? -2 : 0,
            ];
            if ($s['type'] === 'partial') {
                $haov = (float)($s['haov'] ?: 360);
                $vaov = (float)($s['vaov'] ?: max(30, min(180, $haov * $m['height'] / max(1, $m['width']))));
                $conf['haov'] = $haov;
                $conf['vaov'] = $vaov;
                $conf['vOffset'] = (float)$s['voffset'];
                $conf['minPitch'] = -$vaov / 2 + (float)$s['voffset'];
                $conf['maxPitch'] = $vaov / 2 + (float)$s['voffset'];
                if ($haov < 360) {
                    $conf['minYaw'] = -$haov / 2;
                    $conf['maxYaw'] = $haov / 2;
                }
            }
            $bySlug[$s['slug']] = $conf;
            if ($s['is_default'] && !$default) {
                $default = $s['slug'];
            }
        }
        $firstScene = ($first && isset($bySlug[$first])) ? $first : ($default ?: (array_key_first($bySlug) ?? ''));
        return ['default' => ['firstScene' => $firstScene], 'scenes' => $bySlug];
    }

    // ---------- Menus (café + breakfast) ----------

    public static function menu(string $section): array
    {
        return self::memo('menu' . $section, function () use ($section) {
            $cats = DB::all('SELECT * FROM menu_categories WHERE active = 1 AND section = ? ORDER BY sort, id', [$section]);
            if (!$cats) {
                return [];
            }
            $ids = implode(',', array_map('intval', array_column($cats, 'id')));
            $items = DB::all("SELECT * FROM menu_items WHERE active = 1 AND category_id IN ($ids) ORDER BY sort, id");
            media_preload(array_column($items, 'media_id'));
            $by = [];
            foreach ($items as $it) {
                $by[(int)$it['category_id']][] = $it;
            }
            foreach ($cats as &$c) {
                $c['items'] = $by[(int)$c['id']] ?? [];
            }
            return $cats;
        });
    }

    // ---------- Other lists ----------

    public static function attractions(): array
    {
        return self::memo('attr', function () {
            $rows = DB::all('SELECT * FROM attractions WHERE active = 1 ORDER BY sort, id');
            media_preload(array_column($rows, 'media_id'));
            return $rows;
        });
    }

    public static function reviews(): array
    {
        return self::memo('rev', fn() => DB::all('SELECT * FROM reviews WHERE active = 1 ORDER BY sort, id'));
    }

    public static function faqs(): array
    {
        return self::memo('faq', fn() => DB::all('SELECT * FROM faqs WHERE active = 1 ORDER BY sort, id'));
    }

    public static function pages(string $where = ''): array
    {
        return self::memo('pages' . $where, function () use ($where) {
            $sql = 'SELECT id, slug, title, show_in_menu, show_in_footer FROM pages WHERE active = 1';
            if ($where === 'menu') {
                $sql .= ' AND show_in_menu = 1';
            } elseif ($where === 'footer') {
                $sql .= ' AND show_in_footer = 1';
            }
            return DB::all($sql . ' ORDER BY sort, id');
        });
    }

    public static function page(string $slug): ?array
    {
        return DB::one('SELECT * FROM pages WHERE slug = ? AND active = 1', [$slug]);
    }

    public static function slides(): array
    {
        return self::memo('slides', function () {
            $rows = DB::all('SELECT * FROM slides WHERE active = 1 ORDER BY sort, id');
            media_preload(array_column($rows, 'media_id'));
            return $rows;
        });
    }

    /** Home page sections in order, with their data decoded. */
    public static function sections(bool $onlyActive = true): array
    {
        return self::memo('sections' . (int)$onlyActive, function () use ($onlyActive) {
            $rows = DB::all('SELECT * FROM sections' . ($onlyActive ? ' WHERE active = 1' : '') . ' ORDER BY sort, id');
            foreach ($rows as &$r) {
                $r['data'] = json_dec($r['data'], []);
            }
            return $rows;
        });
    }

    public static function section(string $key): array
    {
        foreach (self::sections(false) as $s) {
            if ($s['skey'] === $key) {
                return $s['data'];
            }
        }
        return [];
    }

    /** Navigation entries in the admin-defined order. */
    public static function nav(): array
    {
        $labels = [
            'home' => ['nav.home', ''], 'rooms' => ['nav.rooms', 'rooms'], 'gallery' => ['nav.gallery', 'gallery'],
            'tour' => ['nav.tour', 'tour'], 'breakfast' => ['nav.breakfast', 'breakfast'], 'cafe' => ['nav.cafe', 'cafe'],
            'attractions' => ['nav.attractions', 'attractions'], 'about' => ['nav.about', 'about'], 'contact' => ['nav.contact', 'contact'],
        ];
        $out = [];
        foreach ((array)setting('nav.items', []) as $item) {
            $k = $item['key'] ?? '';
            if (empty($item['on']) || !isset($labels[$k])) {
                continue;
            }
            $out[] = ['key' => $k, 'label' => __($labels[$k][0]), 'url' => url($labels[$k][1]), 'path' => $labels[$k][1]];
        }
        foreach (self::pages('menu') as $p) {
            $out[] = ['key' => 'page-' . $p['slug'], 'label' => tr($p['title']), 'url' => url('page/' . $p['slug']), 'path' => 'page/' . $p['slug']];
        }
        return $out;
    }

    public static function navEnabled(string $key): bool
    {
        foreach ((array)setting('nav.items', []) as $item) {
            if (($item['key'] ?? '') === $key) {
                return !empty($item['on']);
            }
        }
        return true;
    }
}
