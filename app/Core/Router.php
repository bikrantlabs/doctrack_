<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array{0:class-string,1:string}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function dispatch(string $method, string $path): void
    {
        $normalizedPath = $this->normalizePath($path);
        $methodRoutes = $this->routes[$method] ?? [];

        $exactHandler = $methodRoutes[$normalizedPath] ?? null;
        if ($exactHandler !== null) {
            $this->invokeHandler($exactHandler, []);
            return;
        }

        foreach ($methodRoutes as $routePath => $handler) {
            $params = $this->extractRouteParams($routePath, $normalizedPath);
            if ($params === null) {
                continue;
            }

            $this->invokeHandler($handler, $params);
            return;
        }

        http_response_code(404);
        echo 'Page not found';
    }

    private function map(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '//' || $path === '' ? '/' : $path;
    }

    /**
     * @param callable|array{0:class-string,1:string} $handler
     * @param array<int, string> $params
     */
    private function invokeHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler(...$params);
            return;
        }

        [$className, $action] = $handler;
        $controller = new $className();
        $controller->{$action}(...$params);
    }

    /**
     * @return array<int, string>|null
     */
    private function extractRouteParams(string $routePath, string $actualPath): ?array
    {
        if (!str_contains($routePath, '{')) {
            return null;
        }

        $pattern = preg_quote($routePath, '#');
        $pattern = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', '(?P<$1>[^/]+)', $pattern);

        if (!is_string($pattern)) {
            return null;
        }

        if (!preg_match('#^' . $pattern . '$#', $actualPath, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $params[] = $value;
        }

        return $params;
    }
}

