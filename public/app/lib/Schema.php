<?php
/**
 * Database schema, defined once and translated to SQLite or MySQL.
 * migrate() creates missing tables and adds missing columns, so updates are safe to run again.
 *
 * Column types: pk, int, bool, float, str (191 chars), text (long text / JSON), datetime.
 * Fields marked "i18n" store JSON like {"fa": "...", "en": "...", "ar": "..."}.
 */
final class Schema
{
    public static function tables(): array
    {
        return [
            'settings' => [
                'cols' => ['name' => 'str!pk', 'value' => 'text'],
            ],
            'users' => [
                'cols' => [
                    'id' => 'pk', 'username' => 'str', 'name' => 'str', 'email' => 'str', 'password' => 'str',
                    'role' => 'str', 'lang' => 'str', 'last_login' => 'datetime', 'created_at' => 'datetime',
                ],
                'unique' => [['username']],
            ],
            'login_attempts' => [
                'cols' => ['id' => 'pk', 'ip' => 'str', 'username' => 'str', 'created_at' => 'datetime'],
                'index' => [['ip']],
            ],
            'media' => [
                'cols' => [
                    'id' => 'pk', 'kind' => 'str', 'file' => 'str', 'ext' => 'str', 'mime' => 'str',
                    'width' => 'int', 'height' => 'int', 'size' => 'int', 'variants' => 'text', 'lqip' => 'text',
                    'color' => 'str', 'alt' => 'text', 'title' => 'str', 'created_at' => 'datetime',
                ],
            ],
            'rooms' => [
                'cols' => [
                    'id' => 'pk', 'slug' => 'str', 'name' => 'text', 'highlight' => 'text', 'summary' => 'text',
                    'description' => 'text', 'badge' => 'text', 'color' => 'str',
                    'beds' => 'int', 'min_guests' => 'int', 'max_guests' => 'int', 'max_extra' => 'int', 'room_count' => 'int',
                    'size_m2' => 'int', 'floor' => 'text', 'view' => 'text', 'bed_layout' => 'text',
                    'prices' => 'text', 'extra_bed_price' => 'int', 'show_prices' => 'bool', 'price_note' => 'text',
                    'amenities' => 'text', 'features' => 'text', 'cover_id' => 'int', 'gallery' => 'text', 'scene_id' => 'int',
                    'featured' => 'bool', 'active' => 'bool', 'sort' => 'int',
                    'created_at' => 'datetime', 'updated_at' => 'datetime',
                ],
                'unique' => [['slug']],
            ],
            'amenities' => [
                'cols' => [
                    'id' => 'pk', 'icon' => 'str', 'name' => 'text', 'description' => 'text', 'scope' => 'str',
                    'highlight' => 'bool', 'active' => 'bool', 'sort' => 'int',
                ],
            ],
            'gallery_categories' => [
                'cols' => ['id' => 'pk', 'slug' => 'str', 'name' => 'text', 'cover_id' => 'int', 'active' => 'bool', 'sort' => 'int'],
                'unique' => [['slug']],
            ],
            'gallery_items' => [
                'cols' => [
                    'id' => 'pk', 'category_id' => 'int', 'media_id' => 'int', 'caption' => 'text',
                    'active' => 'bool', 'sort' => 'int', 'created_at' => 'datetime',
                ],
                'index' => [['category_id']],
            ],
            'scenes' => [
                'cols' => [
                    'id' => 'pk', 'slug' => 'str', 'title' => 'text', 'description' => 'text', 'type' => 'str',
                    'media_id' => 'int', 'embed_url' => 'str', 'haov' => 'float', 'vaov' => 'float', 'voffset' => 'float',
                    'yaw' => 'float', 'pitch' => 'float', 'hfov' => 'float', 'hotspots' => 'text',
                    'is_default' => 'bool', 'autorotate' => 'bool', 'active' => 'bool', 'sort' => 'int',
                ],
                'unique' => [['slug']],
            ],
            'menu_categories' => [
                'cols' => [
                    'id' => 'pk', 'section' => 'str', 'name' => 'text', 'description' => 'text', 'icon' => 'str',
                    'active' => 'bool', 'sort' => 'int',
                ],
            ],
            'menu_items' => [
                'cols' => [
                    'id' => 'pk', 'category_id' => 'int', 'name' => 'text', 'description' => 'text', 'price' => 'int',
                    'media_id' => 'int', 'tag' => 'text', 'active' => 'bool', 'sort' => 'int',
                ],
                'index' => [['category_id']],
            ],
            'attractions' => [
                'cols' => [
                    'id' => 'pk', 'name' => 'text', 'description' => 'text', 'distance_km' => 'float', 'travel' => 'text',
                    'media_id' => 'int', 'icon' => 'str', 'lat' => 'float', 'lng' => 'float', 'active' => 'bool', 'sort' => 'int',
                ],
            ],
            'reviews' => [
                'cols' => [
                    'id' => 'pk', 'author' => 'text', 'origin' => 'text', 'rating' => 'int', 'body' => 'text',
                    'source' => 'str', 'stay_date' => 'str', 'active' => 'bool', 'sort' => 'int',
                ],
            ],
            'faqs' => [
                'cols' => ['id' => 'pk', 'question' => 'text', 'answer' => 'text', 'active' => 'bool', 'sort' => 'int'],
            ],
            'pages' => [
                'cols' => [
                    'id' => 'pk', 'slug' => 'str', 'title' => 'text', 'excerpt' => 'text', 'body' => 'text', 'cover_id' => 'int',
                    'show_in_menu' => 'bool', 'show_in_footer' => 'bool', 'active' => 'bool', 'sort' => 'int', 'updated_at' => 'datetime',
                ],
                'unique' => [['slug']],
            ],
            'slides' => [
                'cols' => ['id' => 'pk', 'media_id' => 'int', 'title' => 'text', 'subtitle' => 'text', 'active' => 'bool', 'sort' => 'int'],
            ],
            'sections' => [
                'cols' => ['id' => 'pk', 'skey' => 'str', 'active' => 'bool', 'sort' => 'int', 'data' => 'text'],
                'unique' => [['skey']],
            ],
            'messages' => [
                'cols' => [
                    'id' => 'pk', 'name' => 'str', 'email' => 'str', 'phone' => 'str', 'subject' => 'str', 'body' => 'text',
                    'lang' => 'str', 'ip' => 'str', 'user_agent' => 'str', 'is_read' => 'bool', 'created_at' => 'datetime',
                ],
                'index' => [['ip']],
            ],
            'translations' => [
                'cols' => ['id' => 'pk', 'lang' => 'str', 'tkey' => 'str', 'value' => 'text'],
                'unique' => [['lang', 'tkey']],
            ],
            'price_presets' => [
                'cols' => ['id' => 'pk', 'name' => 'str', 'note' => 'str', 'data' => 'text', 'created_at' => 'datetime', 'updated_at' => 'datetime'],
            ],
            'visits' => [
                'cols' => ['day' => 'str!pk', 'views' => 'int'],
            ],
            'visit_paths' => [
                'cols' => ['path' => 'str!pk', 'views' => 'int'],
            ],
        ];
    }

    private static function colSql(string $name, string $type, string $driver): string
    {
        $mysql = $driver === 'mysql';
        $pk = str_ends_with($type, '!pk');
        $type = str_replace('!pk', '', $type);
        $n = DB::ident($name);
        if ($type === 'pk') {
            return $mysql ? "$n INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY" : "$n INTEGER PRIMARY KEY AUTOINCREMENT";
        }
        $map = $mysql
            ? ['int' => 'INT NULL', 'bool' => 'TINYINT(1) NOT NULL DEFAULT 0', 'float' => 'DOUBLE NULL', 'str' => 'VARCHAR(191) NULL', 'text' => 'MEDIUMTEXT NULL', 'datetime' => 'DATETIME NULL']
            : ['int' => 'INTEGER NULL', 'bool' => 'INTEGER NOT NULL DEFAULT 0', 'float' => 'REAL NULL', 'str' => 'TEXT NULL', 'text' => 'TEXT NULL', 'datetime' => 'TEXT NULL'];
        $sql = "$n " . $map[$type];
        if ($pk) {
            $sql = "$n " . ($mysql ? 'VARCHAR(191) NOT NULL PRIMARY KEY' : 'TEXT NOT NULL PRIMARY KEY');
        }
        return $sql;
    }

    public static function createSql(string $table, array $def, string $driver): array
    {
        $cols = [];
        foreach ($def['cols'] as $name => $type) {
            $cols[] = self::colSql($name, $type, $driver);
        }
        $t = DB::ident($table);
        $sql = ["CREATE TABLE IF NOT EXISTS $t (\n  " . implode(",\n  ", $cols) . "\n)" . ($driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '')];
        foreach ($def['unique'] ?? [] as $i => $c) {
            $sql[] = self::indexSql($table, $c, true, $driver);
        }
        foreach ($def['index'] ?? [] as $i => $c) {
            $sql[] = self::indexSql($table, $c, false, $driver);
        }
        return $sql;
    }

    private static function indexSql(string $table, array $cols, bool $unique, string $driver): string
    {
        $name = ($unique ? 'uq_' : 'ix_') . $table . '_' . implode('_', $cols);
        $list = implode(',', array_map([DB::class, 'ident'], $cols));
        $kind = $unique ? 'UNIQUE INDEX' : 'INDEX';
        if ($driver === 'mysql') {
            return "ALTER TABLE " . DB::ident($table) . " ADD $kind " . DB::ident($name) . " ($list)";
        }
        return "CREATE $kind IF NOT EXISTS " . DB::ident($name) . " ON " . DB::ident($table) . " ($list)";
    }

    /** Create missing tables and columns. Returns a list of what changed. */
    public static function migrate(): array
    {
        $driver = DB::$driver;
        $log = [];
        foreach (self::tables() as $table => $def) {
            if (!DB::tableExists($table)) {
                foreach (self::createSql($table, $def, $driver) as $sql) {
                    DB::pdo()->exec($sql);
                }
                $log[] = "created $table";
                continue;
            }
            $have = DB::columns($table);
            foreach ($def['cols'] as $name => $type) {
                if (!in_array($name, $have, true)) {
                    DB::pdo()->exec('ALTER TABLE ' . DB::ident($table) . ' ADD COLUMN ' . self::colSql($name, $type, $driver));
                    $log[] = "added $table.$name";
                }
            }
        }
        return $log;
    }
}
