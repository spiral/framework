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

class RouteTest extends HttpTest
{
    public function testRouteToController()
    {
        $this->http->addRoute(new Route('default', '', DummyController::class . ':index'));

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$response->getBody());
    }

    public function testRenaming()
    {
        $route = new Route('name', '/', 'target');
        $this->assertSame('name', $route->getName());
        $this->assertSame('new-name', $route->withName('new-name')->getName());
    }

    public function testRouteToAction()
    {
        $this->http->addRoute(new Route('default', '', 'action'));

        $this->container->bind('action', new class
        {
            public function __invoke()
            {
                return 'im action';
            }
        });

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('im action', (string)$response->getBody());
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

    public function testHost()
    {
        $route = new Route(
            'test',
            '<name>.example.com/<action>',
            DummyController::class . ':<action>'
        );

        $this->http->addRoute($route->withHost());

        $response = (string)$this->get('http://john.example.com/index')->getBody();
        $this->assertSame('Hello, john.', $response);
    }
}