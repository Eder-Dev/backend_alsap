<?php
declare(strict_types=1);

use Br\Udesc\Alsap\Config\Environment;
use Br\Udesc\Alsap\Config\HttpResponse;
use Br\Udesc\Alsap\Routes\ApiRoutes;
use Br\Udesc\Alsap\Routes\Router;

define('ALSAP_ROOT', dirname(__DIR__));

$autoload = ALSAP_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'Br\\Udesc\\Alsap\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $file = ALSAP_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

Environment::load(ALSAP_ROOT . DIRECTORY_SEPARATOR . '.env');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = Router::stripBasePath($path, getenv('APP_BASE_PATH') ?: '');
if (PHP_SAPI === 'cli-server' && $path !== '/') {
    $requestedFile = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim(
        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rawurldecode($path)),
        DIRECTORY_SEPARATOR,
    ));
    $publicRoot = realpath(__DIR__);
    if ($requestedFile !== false && $publicRoot !== false
        && is_file($requestedFile)
        && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)) {
        return false;
    }
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $path === '/') {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . DIRECTORY_SEPARATOR . 'questoes.html');
    exit;
}

try {
    $router = new Router(ApiRoutes::build());
    Router::send($router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path, Router::body()));
} catch (Throwable $error) {
    Router::sendError($error, $path);
}