<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Routes;

use Br\Udesc\Alsap\Config\HttpResponse;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Throwable;

final class Router
{
    public function __construct(private readonly array $routes)
    {
    }

    public static function stripBasePath(string $path, string $basePath): string
    {
        $basePath = trim($basePath, '/');
        if ($basePath === '') {
            return $path;
        }

        $basePath = '/' . $basePath;
        if ($path === $basePath) {
            return '/';
        }
        if (str_starts_with($path, $basePath . '/')) {
            return substr($path, strlen($basePath));
        }

        return $path;
    }

    public function dispatch(string $method, string $path, array $body): HttpResponse
    {
        $pathMatched = false;
        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if (preg_match($pattern, $path, $matches) !== 1) {
                continue;
            }
            $pathMatched = true;
            if ($method !== $routeMethod) {
                continue;
            }
            return $handler($matches, $body);
        }

        if ($pathMatched) {
            throw new RuntimeException('Método HTTP não permitido.', 405);
        }
        throw new RuntimeException('Endpoint não encontrado.', 404);
    }

    public static function body(): array
    {
        $content = file_get_contents('php://input');
        if ($content === false || trim($content) === '') {
            return [];
        }

        try {
            $decoded = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new InvalidArgumentException('Corpo JSON inválido.');
        }
        if (!$decoded instanceof \stdClass) {
            throw new InvalidArgumentException('O corpo da requisição deve ser um objeto JSON.');
        }
        return get_object_vars($decoded);
    }

    public static function send(HttpResponse $response): never
    {
        http_response_code($response->status);
        header('Content-Type: application/json; charset=utf-8');
        if ($response->status !== 204) {
            echo json_encode($response->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
        exit;
    }

    public static function sendError(Throwable $error, string $path): never
    {
        $status = match (true) {
            $error instanceof InvalidArgumentException => 400,
            $error instanceof PDOException => 500,
            default => in_array($error->getCode(), [404, 405], true) ? $error->getCode() : 500,
        };
        $message = getenv('APP_ENV') === 'production' && $status === 500
            ? 'Erro interno do servidor.'
            : $error->getMessage();
        if ($status === 500) {
            error_log((string) $error);
        }

        self::send(new HttpResponse([
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'status' => $status,
            'error' => match ($status) {
                400 => 'Bad Request',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                default => 'Internal Server Error',
            },
            'message' => $message,
            'path' => $path,
        ], $status));
    }
}