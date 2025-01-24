<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Tests\Router\Stub\HeaderMiddleware;

class MiddlewareTest extends BaseTestingCase
{
    use RouterFactoryTrait;

    public function testRoute(): void
    {
        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware(HeaderMiddleware::class),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame('Value*', $response->getHeaderLine('Header'));

        $r = $router->getRoute('group')->withMiddleware(HeaderMiddleware::class);

        $r = $r->match(new ServerRequest('GET', new Uri('/test')));
        $response = $r->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testRouteRuntime(): void
    {
        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware(new HeaderMiddleware()),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame('Value*', $response->getHeaderLine('Header'));
    }

    public function testRouteArray(): void
    {
        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware([new HeaderMiddleware(), HeaderMiddleware::class]),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame('Value*, Value*', $response->getHeaderLine('Header'));

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testInvalid(): void
    {
        $router = $this->makeRouter();

        $this->expectException(\Throwable::class);
        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware($this),
        );
    }

    public function testInvalid2(): void
    {
        $router = $this->makeRouter();

        $this->expectException(\Throwable::class);
        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware([[]]),
        );
    }

    public function testPipelineException(): void
    {
        $this->expectException(RouteException::class);

        $r = (new Route('/<controller>[/<action>[/<id>]]', new Group([
            'test' => TestController::class,
        ])))->withMiddleware([new HeaderMiddleware(), HeaderMiddleware::class]);
        $r = $r->withUriHandler(new UriHandler(new UriFactory()));

        $r = $r->match(new ServerRequest('GET', new Uri('/test')));
        $response = $r->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testUndefinedMiddleware(): void
    {
        $r = (new Route('/<controller>[/<action>[/<id>]]', new Group([
            'test' => TestController::class,
        ])))->withMiddleware([new HeaderMiddleware(), 'other']);
        $r = $r->withUriHandler(new UriHandler(new UriFactory()));

        $r = $r->withContainer($this->getContainer());

        $r = $r->match(new ServerRequest('GET', new Uri('/test')));

        $this->expectException(NotFoundExceptionInterface::class);
        $r->handle(new ServerRequest('GET', new Uri('/test')));
    }
}
