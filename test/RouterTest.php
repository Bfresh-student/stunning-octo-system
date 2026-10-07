<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Routes\Router;

class RouterDummyController {
    public static bool $actionExecuted = false;
    public static ?string $receivedId = null;

    public function index(): void {
        self::$actionExecuted = true;
    }

    public function edit(string $id): void {
        self::$receivedId = $id;
    }
}

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        RouterDummyController::$actionExecuted = false;
        RouterDummyController::$receivedId = null;
    }

    public function testGetRouteRegistration(): void
    {
        $this->router->get('/test', [RouterDummyController::class, 'index']);
        $routes = $this->router->getRoutes();

        $this->assertCount(1, $routes);
        $this->assertSame('GET', $routes[0]['method']);
        $this->assertSame('/test', $routes[0]['path']);
        $this->assertSame(RouterDummyController::class, $routes[0]['controller']);
        $this->assertSame('index', $routes[0]['action']);
    }

    public function testPostRouteRegistration(): void
    {
        $this->router->post('/submit', [RouterDummyController::class, 'index']);
        $routes = $this->router->getRoutes();

        $this->assertCount(1, $routes);
        $this->assertSame('POST', $routes[0]['method']);
        $this->assertSame('/submit', $routes[0]['path']);
    }

    public function testDispatchExecutesMatchingAction(): void
    {
        $this->router->get('/home', [RouterDummyController::class, 'index']);

        $this->router->dispatch('/home', 'GET');

        $this->assertTrue(RouterDummyController::$actionExecuted);
    }

    public function testDispatchPassesDynamicParameters(): void
    {
        $this->router->get('/item/{id}', [RouterDummyController::class, 'edit']);

        $this->router->dispatch('/item/42', 'GET');

        $this->assertSame('42', RouterDummyController::$receivedId);
    }

    public function testDispatchRenders404ForUnknownRoute(): void
    {
        $this->router->get('/exists', [RouterDummyController::class, 'index']);

        ob_start();
        $this->router->dispatch('/does-not-exist', 'GET');
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('404', $output);
    }
}

