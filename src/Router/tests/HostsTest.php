<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;
use Nyholm\Psr7\ServerRequest;

class HostsTest extends BaseTestCase
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '://<id>.com/',
            new Action(TestController::class, 'test')
        ));

        $match = $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '//<id>.com/',
            new Action(TestController::class, 'test')
        ));

        $this->assertNotNull(
            $r = $router->handle(new ServerRequest('GET', 'http://domain.com/'))
        );

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('hello world', (string)$r->getBody());

        $this->assertNotNull(
            $r = $router->handle(new ServerRequest('GET', 'https://domain.com/'))
        );

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('hello world', (string)$r->getBody());
    }
}
