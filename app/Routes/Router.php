<?php

declare(strict_types=1);

namespace App\Routes;

use App\Utils\AppLogger;
use PDO;

class Router
{
    private array $routes = [];
    private ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function get(string $path, array $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $action, $middlewares);
    }

    public function post(string $path, array $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $action, $middlewares);
    }

    private function addRoute(string $method, string $path, array $action, array $middlewares): void
    {
        $this->routes[] = [
            'method'      => $method,
            'path'        => '/' . trim($path, '/'),
            'controller'  => $action[0],
            'action'      => $action[1],
            'middlewares' => $middlewares
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function resolveController(string $controllerClass): object
    {
        $reflector = new \ReflectionClass($controllerClass);
        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $controllerClass();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();

                if ($typeName === PDO::class) {
                    $dependencies[] = $this->pdo;
                } else {
                    $dependencies[] = $this->resolveController($typeName);
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                $dependencies[] = null;
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    public function dispatch(string $uri, string $method): void
    {
        $parsedUri = parse_url($uri, PHP_URL_PATH) ?? '/';

        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $base = ($scriptName === '/' || $scriptName === '\\') ? '' : str_replace('\\', '/', $scriptName);
        if ($base !== '' && str_starts_with($parsedUri, $base)) {
            $parsedUri = substr($parsedUri, strlen($base));
        }
        $parsedUri = '/' . trim($parsedUri, '/');
        if ($parsedUri === '') {
            $parsedUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $parsedUri, $matches)) {
                array_shift($matches);

                foreach ($route['middlewares'] as $middleware) {
                    $mwInstance = new $middleware();
                    if (method_exists($mwInstance, 'handle')) {
                        $mwInstance->handle();
                    }
                }

                $controller = $this->resolveController($route['controller']);
                $action = $route['action'];

                $refMethod = new \ReflectionMethod($controller, $action);
                $methodParams = $refMethod->getParameters();
                $typedMatches = [];

                foreach ($matches as $index => $value) {
                    if (isset($methodParams[$index])) {
                        $paramType = $methodParams[$index]->getType();
                        if ($paramType instanceof \ReflectionNamedType) {
                            $typeName = $paramType->getName();
                            if ($typeName === 'int' && is_numeric($value)) {
                                $typedMatches[] = (int) $value;
                                continue;
                            } elseif ($typeName === 'float' && is_numeric($value)) {
                                $typedMatches[] = (float) $value;
                                continue;
                            }
                        }
                    }
                    $typedMatches[] = $value;
                }

                $controller->{$action}(...$typedMatches);
                return;
            }
        }

        AppLogger::warning('Route non trouvée (404)', [
            'uri'    => $parsedUri,
            'method' => $method
        ]);
        http_response_code(404);
        echo "<h1>Page non trouvée (404)</h1>";
    }
}
