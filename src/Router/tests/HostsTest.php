<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\ServerRequest;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;

final class HostsTest extends BaseTestCase
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '://<id>.com/',
            new Action(TestController::class, 'test'),
        ));

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '//<id>.com/',
            new Action(TestController::class, 'test'),
        ));

        self::assertNotNull($r = $router->handle(new ServerRequest('GET', 'http://domain.com/')));

        self::assertSame(200, $r->getStatusCode());
        self::assertSame('hello world', (string) $r->getBody());

        self::assertNotNull($r = $router->handle(new ServerRequest('GET', 'https://domain.com/')));

        self::assertSame(200, $r->getStatusCode());
        self::assertSame('hello world', (string) $r->getBody());
    }
}
