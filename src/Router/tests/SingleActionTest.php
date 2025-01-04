<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\RouteException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class SingleActionTest extends BaseTestCase
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string)$response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string)$response->getBody());
    }

    public function testVerbRoute(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            (new Route('/test', new Action(TestController::class, 'test')))->withVerbs('POST')
        );

        $router->handle(new ServerRequest('GET', new Uri('/test')));
    }

    public function testVerbRouteValid(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            (new Route('/test', new Action(TestController::class, 'test')))->withVerbs('POST')
        );

        $response = $router->handle(new ServerRequest('POST', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string)$response->getBody());
    }

    public function testEchoed(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'echo'))
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('echoed', (string)$response->getBody());
    }

    public function testAutoFill(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>', new Action(TestController::class, 'echo'))
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/echo')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('echoed', (string)$response->getBody());

        $e = null;
        try {
            $router->handle(new ServerRequest('GET', new Uri('/test')));
        } catch (UndefinedRouteException $e) {
        }

        self::assertNotNull($e, 'Autofill not fired');
    }

    public function testVerbException(): void
    {
        $this->expectException(RouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            (new Route('/test', new Action(TestController::class, 'test')))->withVerbs('other')
        );
    }

    public function testParametrizedActionRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test/<id:\d+>', new Action(TestController::class, 'id'))
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test/100')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('100', (string)$response->getBody());
    }

    public function testParametrizedActionRouteNotFound(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test/<id:\d+>', new Action(TestController::class, 'id'))
        );

        $router->handle(new ServerRequest('GET', new Uri('/test/abc')));
    }

    public function testUriGeneration(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test/<id>', new Action(TestController::class, 'id'))
        );

        $uri = $router->uri('action');
        self::assertSame('/test', $uri->getPath());

        $uri = $router->uri('action', ['id' => 100]);
        self::assertSame('/test/100', $uri->getPath());
    }

    public function testWrongActionRoute(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $router->handle(new ServerRequest('GET', new Uri('/other')));
    }
}
