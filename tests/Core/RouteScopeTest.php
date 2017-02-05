<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Routing\Route;
use Spiral\Http\Routing\RouteInterface;
use Spiral\Tests\Http\HttpTest;
use TestApplication\Controllers\DummyController;
use Zend\Diactoros\ServerRequest;

class RouteScopeTest extends HttpTest
{
    public function testRouteThoughtAttribute()
    {
        $route = new Route('default', '', DummyController::class . ':route');

        $this->container->bind(
            ServerRequestInterface::class,
            (new ServerRequest())->withAttribute('route', $route)
        );

        $this->assertSame($route, $this->container->get(RouteInterface::class));
    }

    public function testAccessRouteInController()
    {
        $this->http->addRoute(new Route('default', '', DummyController::class . ':route'));

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('default', (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testRouteWhenNoRequest()
    {
        $this->container->get(RouteInterface::class);
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testRouteWhenNoRequestAttribute()
    {
        $this->container->bind(ServerRequestInterface::class, new ServerRequest());

        $this->container->get(RouteInterface::class);
    }
}