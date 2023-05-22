<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\RouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class MiddlewareTest extends BaseTestCase
{
    public function testRoute(): void
    {
        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware(HeaderMiddleware::class)
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*', $response->getHeaderLine('Header'));

        $r = $router->getRoute('group')->withMiddleware(HeaderMiddleware::class);

        $r = $r->match(new ServerRequest('GET', new Uri('/test')));
        $response = $r->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testRouteRuntime(): void
    {
        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware(new HeaderMiddleware())
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*', $response->getHeaderLine('Header'));
    }

    public function testRouteArray(): void
    {
        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware([new HeaderMiddleware(), HeaderMiddleware::class])
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*, Value*', $response->getHeaderLine('Header'));

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testInvalid(): void
    {
        $this->expectException(RouteException::class);

        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware($this)
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testInvalid2(): void
    {
        $this->expectException(RouteException::class);

        $router = $this->makeRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware([[]])
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*, Value*', $response->getHeaderLine('Header'));
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
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
        $this->assertSame('Value*, Value*', $response->getHeaderLine('Header'));
    }

    public function testPipelineExceptionMiddleware(): void
    {
        $this->expectException(RouteException::class);

        $r = (new Route('/<controller>[/<action>[/<id>]]', new Group([
            'test' => TestController::class,
        ])))->withMiddleware([new HeaderMiddleware(), 'other']);
        $r = $r->withUriHandler(new UriHandler(new UriFactory()));

        $r = $r->withContainer($this->container);

        $r = $r->match(new ServerRequest('GET', new Uri('/test')));
        $r->handle(new ServerRequest('GET', new Uri('/test')));
    }
}
