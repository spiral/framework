<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Exception\UriHandlerException;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Fixtures\TestController;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class GroupTest extends BaseTest
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>/<action>', new Group([
                'test' => TestController::class,
            ]))
        );

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/test/id/900')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('900', (string)$response->getBody());
    }

    public function testRouteOther(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $router->handle(new ServerRequest('GET', new Uri('/other')));
    }

    public function testUriInvalid(): void
    {
        $this->expectException(UriHandlerException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->uri('group/test');
    }

    public function testUriInvalidNoAction(): void
    {
        $this->expectException(UriHandlerException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->getRoute('group')->uri(['controller' => 'test']);
    }

    public function testClientException(): void
    {
        $this->expectException(NotFoundException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $router->handle(new ServerRequest('GET', new Uri('/test/other')));
    }
}
