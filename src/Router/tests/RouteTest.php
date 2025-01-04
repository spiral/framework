<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Route;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Spiral\Tests\Router\Stub\TestMiddleware;

class RouteTest extends BaseTestCase
{
    public function testEmptyPrefix(): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        self::assertSame('', $route->getUriHandler()->getPrefix());
    }

    #[DataProvider('prefixesDataProvider')]
    public function testPrefix(string $prefix, string $expected): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withUriHandler((new UriHandler(new UriFactory()))->withPrefix($prefix));

        self::assertSame($expected, $route->getUriHandler()->getPrefix());
    }

    public function testContainerException(): void
    {
        $this->expectException(RouteException::class);

        $route = new Route('/action', Call::class);
        $route->handle(new ServerRequest('GET', ''));
    }

    #[DataProvider('middlewaresDataProvider')]
    public function testWithMiddleware(mixed $middleware): void
    {
        $route = new Route('/action', Call::class);
        $route = $route->withMiddleware($middleware)->withContainer($this->container);

        (new \ReflectionMethod($route, 'makePipeline'))->invoke($route);

        $p = $this->getProperty($route, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        self::assertCount(1, $m);
        // Because of the pipeline is lazy
        self::assertSame($middleware, $m[0]);
    }

    public static function prefixesDataProvider(): \Traversable
    {
        yield ['something', 'something'];
        yield ['/something/', 'something'];
        yield ['//something/', 'something'];
        yield ['something//', 'something'];
        yield ['something/other', 'something/other'];
        yield ['/something/other/', 'something/other'];
    }
}
