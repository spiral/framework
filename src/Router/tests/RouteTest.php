<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\RouteException;
use Spiral\Router\Route;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Spiral\Tests\Router\Stub\TestMiddleware;

class RouteTest extends BaseTest
{
    public function testEmptyPrefix(): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame('', $route->getUriHandler()->getPrefix());
    }

    /**
     * @dataProvider prefixesDataProvider
     */
    public function testPrefix(string $prefix, string $expected): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withUriHandler((new UriHandler(new UriFactory()))->withPrefix($prefix));

        $this->assertSame($expected, $route->getUriHandler()->getPrefix());
    }

    public function testContainerException(): void
    {
        $this->expectException(RouteException::class);

        $route = new Route('/action', Call::class);
        $route->handle(new ServerRequest('GET', ''));
    }

    /** @dataProvider middlewaresDataProvider */
    public function testWithMiddleware(mixed $middleware): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withMiddleware($middleware)->withContainer($this->container);

        (new \ReflectionMethod($route, 'makePipeline'))->invoke($route);

        $p = $this->getProperty($route, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        $this->assertCount(1, $m);
        $this->assertInstanceOf(TestMiddleware::class, $m[0]);
    }

    public function prefixesDataProvider(): \Traversable
    {
        yield ['something', 'something'];
        yield ['/something/', 'something'];
        yield ['//something/', 'something'];
        yield ['something//', 'something'];
        yield ['something/other', 'something/other'];
        yield ['/something/other/', 'something/other'];
    }
}
