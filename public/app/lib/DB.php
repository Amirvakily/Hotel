<?php
/**
 * Thin PDO wrapper. Works with SQLite (default, zero setup) and MySQL/MariaDB.
 */
final class DB
{
    private static ?PDO $pdo = null;
    public static string $driver = 'sqlite';

    public static function connect(array $cfg): PDO
    {
        $db = $cfg['db'] ?? ['driver' => 'sqlite'];
        self::$driver = $db['driver'] ?? 'sqlite';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        if (self::$driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $db['host'] ?? 'localhost',
                (int)($db['port'] ?? 3306),
                $db['name'] ?? ''
            );
            $pdo = new PDO($dsn, $db['user'] ?? '', $db['pass'] ?? '', $options);
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("SET time_zone = '+00:00'");
        } else {
            $path = $db['path'] ?? (STORAGE . '/database.sqlite');
            if ($path !== '' && $path[0] !== '/' && !preg_match('~^[A-Za-z]:\\\\~', $path)) {
                $path = ROOT . '/' . $path;
            }
            $pdo = new PDO('sqlite:' . $path, null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
            // WAL gives better concurrency; some shared hosts block it, so ignore failures.
            try {
                $pdo->exec('PRAGMA journal_mode = WAL');
            } catch (Throwable $e) {
            }
        }
        self::$pdo = $pdo;
        return $pdo;
    }

    public static function pdo(): PDO
    {
        if (!self::$pdo) {
            $cfg = $GLOBALS['VENUS_CONFIG'] ?? null;
            if (!$cfg) {
                throw new RuntimeException('Database is not configured. Run the installer.');
            }
            self::connect($cfg);
        }
        return self::$pdo;
    }

    public static function setPdo(PDO $pdo, string $driver): void
    {
        self::$pdo = $pdo;
        self::$driver = $driver;
    }

    public static function q(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::q($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::q($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function val(string $sql, array $params = [])
    {
        $v = self::q($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = 'INSERT INTO ' . self::ident($table) . ' (' . implode(',', array_map([self::class, 'ident'], $cols)) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')';
        self::q($sql, array_values($data));
        return (int)self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $params = []): int
    {
        if (!$data) {
            return 0;
        }
        $sets = [];
        foreach (array_keys($data) as $c) {
            $sets[] = self::ident($c) . ' = ?';
        }
        $sql = 'UPDATE ' . self::ident($table) . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
        return self::q($sql, array_merge(array_values($data), $params))->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        return self::q('DELETE FROM ' . self::ident($table) . ' WHERE ' . $where, $params)->rowCount();
    }

    /** Insert or add to a counter row (used by the visit counter). */
    public static function bump(string $table, string $keyCol, string $key, string $countCol, int $by = 1): void
    {
        $t = self::ident($table);
        $k = self::ident($keyCol);
        $c = self::ident($countCol);
        if (self::$driver === 'mysql') {
            self::q("INSERT INTO $t ($k, $c) VALUES (?, ?) ON DUPLICATE KEY UPDATE $c = $c + ?", [$key, $by, $by]);
        } else {
            self::q("INSERT INTO $t ($k, $c) VALUES (?, ?) ON CONFLICT($k) DO UPDATE SET $c = $c + ?", [$key, $by, $by]);
        }
    }

    public static function tx(callable $fn)
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $r = $fn();
            $pdo->commit();
            return $r;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function ident(string $name): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('Bad identifier: ' . $name);
        }
        return self::$driver === 'mysql' ? "`$name`" : "\"$name\"";
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function tableExists(string $table): bool
    {
        if (self::$driver === 'mysql') {
            return (bool)self::val('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$table]);
        }
        return (bool)self::val("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
    }

    public static function columns(string $table): array
    {
        if (self::$driver === 'mysql') {
            return array_column(self::all('SHOW COLUMNS FROM ' . self::ident($table)), 'Field');
        }
        return array_column(self::all('PRAGMA table_info(' . self::ident($table) . ')'), 'name');
    }
}
