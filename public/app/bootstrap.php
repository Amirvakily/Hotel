<?php
/**
 * Venus Hotel website – bootstrap.
 * Loaded by every entry point (index.php, install/index.php, CLI scripts).
 */
declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    http_response_code(500);
    exit('Venus Hotel needs PHP 8.0 or newer. This server runs PHP ' . PHP_VERSION . '.');
}

define('VENUS', true);
define('VENUS_VERSION', '1.0.0');
define('ROOT', dirname(__DIR__));          // the web root (public/)
define('APP', __DIR__);                    // application code
define('STORAGE', ROOT . '/storage');      // database, cache, logs, config
define('UPLOADS', ROOT . '/uploads');      // user uploaded media
define('IS_CLI', PHP_SAPI === 'cli');

mb_internal_encoding('UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE . '/logs/php-error.log');

spl_autoload_register(static function (string $class): void {
    $file = APP . '/lib/' . $class . '.php';
    if (is_file($file)) {
        require $file;
        return;
    }
    $file = APP . '/controllers/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP . '/lib/helpers.php';

/** Loaded configuration (null until the installer has run). */
$GLOBALS['VENUS_CONFIG'] = is_file(STORAGE . '/config.php') ? (require STORAGE . '/config.php') : null;

$tz = $GLOBALS['VENUS_CONFIG']['timezone'] ?? 'Asia/Tehran';
date_default_timezone_set($tz);

if (!empty($GLOBALS['VENUS_CONFIG']['debug'])) {
    ini_set('display_errors', IS_CLI ? '1' : '0');
}
