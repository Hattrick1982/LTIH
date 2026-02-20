<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /**
     * @var array<string, array<int, array{regex:string,paramNames:array<int,string>,handler:Closure}>>
     */
    private array $routes = [];
    private ?Closure $notFoundHandler = null;

    public function get(string $pattern, Closure $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, Closure $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function delete(string $pattern, Closure $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    public function setNotFoundHandler(Closure $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    private function add(string $method, string $pattern, Closure $handler): void
    {
        [$regex, $paramNames] = $this->compilePattern($pattern);
        $this->routes[$method][] = [
            'regex' => $regex,
            'paramNames' => $paramNames,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = rtrim($request->path(), '/') ?: '/';

        $candidateRoutes = $this->routes[$method] ?? [];
        if ($method === 'HEAD') {
            $candidateRoutes = array_merge($candidateRoutes, $this->routes['GET'] ?? []);
        }

        foreach ($candidateRoutes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['paramNames'] as $paramName) {
                $params[$paramName] = $matches[$paramName] ?? null;
            }

            $response = ($route['handler'])($request, $params);

            if (!$response instanceof Response) {
                return Response::json(['error' => 'Route handler gaf geen geldige response terug.'], 500);
            }

            return $response;
        }

        if ($this->notFoundHandler instanceof Closure) {
            $response = ($this->notFoundHandler)($request, []);
            if ($response instanceof Response) {
                return $response;
            }
        }

        return Response::html('Pagina niet gevonden', 404);
    }

    /** @return array{0:string,1:array<int,string>} */
    private function compilePattern(string $pattern): array
    {
        $pattern = rtrim($pattern, '/') ?: '/';
        $paramNames = [];

        if ($pattern === '/') {
            return ['#^/$#', []];
        }

        $segments = explode('/', ltrim($pattern, '/'));
        $regexSegments = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([a-zA-Z0-9_]+)\}$/', $segment, $matches)) {
                $paramNames[] = $matches[1];
                $regexSegments[] = '(?<' . $matches[1] . '>[^/]+)';
                continue;
            }

            $regexSegments[] = preg_quote($segment, '#');
        }

        return ['#^/' . implode('/', $regexSegments) . '$#', $paramNames];
    }
}
