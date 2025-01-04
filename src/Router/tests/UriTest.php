<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Fixtures\TestController;

class UriTest extends BaseTestCase
{
    public function testCastRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $uri = $router->uri('group/test:test');
        self::assertSame('/test/test', $uri->getPath());
    }

    public function testQuery(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $uri = $router->uri('group/test:id', ['id' => 100, 'data' => 'hello']);
        self::assertSame('/test/id/100', $uri->getPath());
        self::assertSame('data=hello', $uri->getQuery());
    }

    public function testDirect(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $uri = $router->getRoute('group')->uri(['test', 'id', 100]);
        self::assertSame('/test/id/100', $uri->getPath());
    }

    public function testSlug(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $uri = $router->getRoute('group')->uri(['test', 'id', 100, 'Hello World']);
        self::assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testSlugDefault(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $uri = $router->uri('test:id', ['id' => 100, 'title' => 'Hello World']);
        self::assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testSlugNoDefault(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();

        $uri = $router->uri('test:id', ['id' => 100, 'title' => 'Hello World']);
        self::assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testObject(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $uri = $router->uri('test:id', [
            'id' => 100,
            'title' => new class implements \Stringable {
                public function __toString()
                {
                    return 'hello-world';
                }
            },
        ]);

        self::assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    #[DataProvider('provideSegmentInDifferentLanguages')]
    public function testCustomPathSegmentEncoder(string $segment, string $expected): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])),
        );

        $route = $router->getRoute('group');
        $uriHandler = $route->getUriHandler()
            ->withPathSegmentEncoder(static fn(string $segment): string => \rawurlencode($segment));
        $route = $route->withUriHandler($uriHandler);

        $uri = $route->uri(['controller' => 'test', 'action' => $segment]);
        self::assertSame($expected, $uri->getPath());
    }

    public static function provideSegmentInDifferentLanguages(): iterable
    {
        yield 'English' => ['test', '/test/test'];
        yield 'Russian' => ['тест', '/test/%D1%82%D0%B5%D1%81%D1%82'];
        yield 'Japanese' => ['テスト', '/test/%E3%83%86%E3%82%B9%E3%83%88'];
        yield 'Chinese' => ['测试', '/test/%E6%B5%8B%E8%AF%95'];
    }
}
