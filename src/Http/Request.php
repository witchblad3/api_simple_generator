<?php
declare(strict_types=1);

namespace App\Http;

final class Request
{
    public function __construct(
        public string $method,
        public string $path,
        public array $query,
        public string $rawBody,
        public string $requestId,
    ) {}

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string)(parse_url($uri, PHP_URL_PATH) ?: '/');

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $requestId = bin2hex(random_bytes(16));

        return new self(
            $method,
            $path,
            $_GET ?? [],
            (string) file_get_contents('php://input'),
            $requestId
        );
    }

    public function getQueryString(string $key): ?string
    {
        $value = $this->query[$key] ?? null;
        return is_string($value) ? $value : null;
    }
}
