<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

class MultipleActionsTest extends BaseTest
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Action(TestController::class, ['test', 'id']))
        );

        $router->handle(new ServerRequest());
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Action(TestController::class, ['test', 'id']))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/id/900')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('900', (string)$response->getBody());
    }

    public function testUriGeneration(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Action(TestController::class, ['test', 'id']))
        );

        $uri = $router->uri('action/test');
        $this->assertSame('/test', $uri->getPath());

        $uri = $router->uri('action/id', ['id' => 100]);
        $this->assertSame('/id/100', $uri->getPath());
    }
}
