<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Exception\UriHandlerException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Fixtures\TestController;

class UriTest extends BaseTestCase
{
    public static function providePatternsWithRequiredSegments(): iterable
    {
        yield ['<controller>[/<section>[/<ext>]]/test/<id>', ['controller' => 'test', 'id' => 1], '/test/test/1'];
        yield ['/articles/<id>[/<section>]', ['id' => 1], '/articles/1'];
        yield ['/articles/<id>', ['id' => 1], '/articles/1'];
        yield ['/articles/<id>/edit', ['id' => 1], '/articles/1/edit'];
        yield ['/articles/<id>/edit/<section>', ['id' => 1, 'section' => 'test'], '/articles/1/edit/test'];
        yield ['/articles/<id>/edit/[<section>/]<path>', ['id' => 1, 'path' => 'test'], '/articles/1/edit/test'];
        yield ['/articles/<id:int>', ['id' => 1], '/articles/1'];
        yield ['/articles/<id:\d+>', ['id' => 1], '/articles/1'];
        yield ['/<path:.*>', ['path' => 'test'], '/test'];
        yield ['/do/<method:login|logout>', ['method' => 'login'], '/do/login'];
        yield ['//<sub>.domain.com/[<section>]', ['sub' => 'test'], 'test.domain.com'];
        yield ['//<sub>.domain.com/', ['sub' => 'test'], 'test.domain.com'];
    }

    public static function provideSegmentInDifferentLanguages(): iterable
    {
        yield 'English' => ['test', '/test/test'];
        yield 'Russian' => ['тест', '/test/%D1%82%D0%B5%D1%81%D1%82'];
        yield 'Japanese' => ['テスト', '/test/%E3%83%86%E3%82%B9%E3%83%88'];
        yield 'Chinese' => ['测试', '/test/%E6%B5%8B%E8%AF%95'];
    }

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

    #[DataProvider('providePatternsWithRequiredSegments')]
    public function testRouteRequiredSegmentsNoStrict(string $pattern): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'article',
            new Route(
                pattern: $pattern,
                target: new Action(
                    controller: TestController::class,
                    action: 'id',
                ),
            ),
        );

        $route = $router->getRoute('article');

        $uriHandler = $route->getUriHandler()->withPathSegmentEncoder(
            static fn(string $segment): string => \rawurlencode($segment),
        );
        $route = $route->withUriHandler($uriHandler);

        self::assertNotNull($route->uri());
    }

    #[DataProvider('providePatternsWithRequiredSegments')]
    public function testRouteRequiredSegments(string $pattern): void
    {
        $this->expectException(UriHandlerException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'article',
            new Route(
                pattern: $pattern,
                target: new Action(
                    controller: TestController::class,
                    action: 'id',
                ),
            ),
        );

        $route = $router->getRoute('article');

        $uriHandler = $route->getUriHandler()->withPathSegmentEncoder(
            static fn(string $segment): string => \rawurlencode($segment),
        );
        $uriHandler->setStrict(true);
        $route = $route->withUriHandler($uriHandler);

        $route->uri();
    }

    #[DataProvider('providePatternsWithRequiredSegments')]
    public function testRouteOptionalSegments(string $pattern, array $params, string $expected): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'article',
            new Route(
                pattern: $pattern,
                target: new Action(
                    controller: TestController::class,
                    action: 'id',
                ),
            ),
        );

        $route = $router->getRoute('article');

        $uriHandler = $route->getUriHandler()->withPathSegmentEncoder(
            static fn(string $segment): string => \rawurlencode($segment),
        );
        $uriHandler->setStrict(true);
        $route = $route->withUriHandler($uriHandler);

        self::assertSame($expected, (string) $route->uri($params));
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
        $uriHandler = $route
            ->getUriHandler()
            ->withPathSegmentEncoder(static fn(string $segment): string => \rawurlencode($segment));
        $route = $route->withUriHandler($uriHandler);

        $uri = $route->uri(['controller' => 'test', 'action' => $segment]);
        self::assertSame($expected, $uri->getPath());
    }
}
