<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Routing\Route;
use Spiral\Http\Routing\RouteInterface;
use TestApplication\Controllers\DummyController;

class RoterScopeTest extends HttpTest
{
    public function testRouteToController()
    {
        $this->http->addRoute(new Route('default', '', DummyController::class . ':index'));

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$response->getBody());
    }

    public function testRouteToClosure()
    {
        $this->http->addRoute(new Route('default', '', function () {
            return 'hello world';
        }));

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
    }

    public function testRouteInAttributes()
    {
        $this->http->addRoute(new Route('default', '', function (ServerRequestInterface $r) {
            $this->assertArrayHasKey(
                'route',
                $r->getAttributes()
            );

            $this->assertInstanceOf(RouteInterface::class, $r->getAttribute('route'));
        }));

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNoRoutes()
    {
        $response = $this->get('http://sample.com/');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDefaultFallback()
    {
        $this->http->defaultRoute(new Route('default', '', DummyController::class . ':index'));

        $response = $this->get('http://sample.com/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$response->getBody());
    }
}