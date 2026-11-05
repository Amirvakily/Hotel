<?php
/**
 * Full-site backup as a zip: data.json (every table) + the uploads folder.
 * The JSON format is the same for SQLite and MySQL, so a backup can also move a site between them.
 */
final class Backup
{
    private const MEDIA_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function create(): string
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException(__('admin.backup_nozip'));
        }
        @set_time_limit(300);
        $dir = STORAGE . '/backups';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/backup-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.zip';
        $data = ['version' => VENUS_VERSION, 'created' => date('c'), 'tables' => []];
        foreach (array_keys(Schema::tables()) as $t) {
            if (in_array($t, ['login_attempts'], true)) {
                continue;
            }
            $data['tables'][$t] = DB::all('SELECT * FROM ' . DB::ident($t));
        }
        $zip = new ZipArchive();
        if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Cannot create ' . $file);
        }
        $zip->addFromString('data.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(UPLOADS, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $ext = strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION));
            if ($f->isFile() && in_array($ext, self::MEDIA_EXT, true)) {
                $rel = 'uploads/' . ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen(UPLOADS))), '/');
                $zip->addFile($f->getPathname(), $rel);
                $zip->setCompressionName($rel, ZipArchive::CM_STORE); // images are already compressed
            }
        }
        $zip->close();
        return $file;
    }

    /** @return int number of rows restored */
    public static function restore(string $zipPath): int
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException(__('admin.backup_nozip'));
        }
        @set_time_limit(300);
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException(__('admin.backup_bad'));
        }
        $json = $zip->getFromName('data.json');
        $data = $json ? json_decode($json, true) : null;
        if (!is_array($data) || !isset($data['tables']) || !is_array($data['tables'])) {
            $zip->close();
            throw new RuntimeException(__('admin.backup_bad'));
        }
        $schema = Schema::tables();
        $rows = 0;
        DB::tx(function () use ($data, $schema, &$rows) {
            foreach ($data['tables'] as $t => $list) {
                if (!isset($schema[$t]) || !is_array($list)) {
                    continue;
                }
                $cols = array_keys($schema[$t]['cols']);
                DB::q('DELETE FROM ' . DB::ident($t));
                foreach ($list as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $clean = array_intersect_key($r, array_flip($cols));
                    if ($clean) {
                        DB::insert($t, $clean);
                        $rows++;
                    }
                }
            }
        });
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string)$zip->getNameIndex($i);
            if (!str_starts_with($name, 'uploads/') || str_contains($name, '..') || str_ends_with($name, '/')) {
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, self::MEDIA_EXT, true)) {
                continue;
            }
            $rel = substr($name, 8);
            if (!preg_match('~^[A-Za-z0-9/_\-.]+$~', $rel)) {
                continue;
            }
            $target = UPLOADS . '/' . $rel;
            if (!is_dir(dirname($target))) {
                @mkdir(dirname($target), 0755, true);
            }
            $stream = $zip->getStream($name);
            if ($stream) {
                $out = fopen($target, 'wb');
                stream_copy_to_stream($stream, $out);
                fclose($out);
                fclose($stream);
            }
        }
        $zip->close();
        Settings::reset();
        return $rows;
    }
}
