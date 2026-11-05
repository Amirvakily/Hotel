<?php
/**
 * Router for PHP's built-in web server (local testing only):
 *   php -S localhost:8000 router.php
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('~^/(app|storage)(/|$)~', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if (preg_match('~^/uploads/.*\.php~i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false; // serve the file as-is
}
if (preg_match('~^/install/?$~', $path)) {
    $_SERVER['SCRIPT_NAME'] = '/install/index.php';
    require __DIR__ . '/install/index.php';
    return true;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
