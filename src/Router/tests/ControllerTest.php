<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Controller;
use Spiral\Tests\Router\Fixtures\TestController;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class ControllerTest extends BaseTest
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Controller(TestController::class))
        );

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Controller(TestController::class))
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/echo')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('echoed', (string)$response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/id/888')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('888', (string)$response->getBody());
    }

    public function testUriGeneration(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Controller(TestController::class))
        );

        $uri = $router->uri('action/test');
        $this->assertSame('/test', $uri->getPath());

        $uri = $router->uri('action/id', ['id' => 100]);
        $this->assertSame('/id/100', $uri->getPath());
    }

    public function testClientException(): void
    {
        $this->expectException(NotFoundException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Controller(TestController::class))
        );

        $router->handle(new ServerRequest('GET', new Uri('/other')));
    }
}
