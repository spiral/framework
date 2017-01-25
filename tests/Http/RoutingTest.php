<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\Routing\Route;
use TestApplication\Controllers\DummyController;

class RoutingTest extends HttpTest
{
    public function testRouteToController()
    {
        $this->http->addRoute(new Route('default', '', DummyController::class . ':index'));

        $response = $this->get('http://sample.com/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, John.', (string)$response->getBody());
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
        $this->assertSame('Hello, John.', (string)$response->getBody());
    }
}