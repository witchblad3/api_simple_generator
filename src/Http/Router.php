<?php
declare(strict_types=1);

namespace App\Http;

use App\Support\HttpException;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        if ($path === '') {
            $path = '/';
        }

        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $methodRoutes = $this->routes[$request->method] ?? null;
        if ($methodRoutes === null) {
            throw new HttpException(405, 'Method Not Allowed');
        }

        $handler = $methodRoutes[$request->path] ?? null;
        if ($handler === null) {
            throw new HttpException(404, 'Route not found');
        }

        $response = $handler($request);

        if (!$response instanceof Response) {
            throw new HttpException(500, 'Handler must return Response');
        }

        return $response;
    }
}
