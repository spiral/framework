<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\TargetException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Namespaced;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class NamespacedTest extends BaseTestCase
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route(
                '/<controller>/<action>',
                new Namespaced('Spiral\Tests\Router\Fixtures'),
            ),
        );

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route(
                '/<controller>[/<action>[/<id>]]',
                new Namespaced('Spiral\Tests\Router\Fixtures'),
            ),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/test/id/900')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('900', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/other/action')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('action!', (string) $response->getBody());
    }

    public function testBypass(): void
    {
        $this->expectException(TargetException::class);

        $n = new Namespaced('Spiral\Tests\Router\Fixtures');

        $n->getHandler($this->getContainer(), [
            'controller' => 'secret/controller',
            'action'     => null,
        ]);
    }
}
