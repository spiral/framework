<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\RESTfulCore;
use Spiral\Http\Routing\ControllersRoute;
use TestApplication\Controllers\DummyController;
use TestApplication\Controllers\MagicController;

class ControllerRouteTest extends HttpTest
{
    public function testDefaultRoute()
    {
        $route = new ControllersRoute(
            'controllers',
            '/[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $route = $route->withControllers(['m' => 'magic'])->withDefaults(['controller' => 'dummy']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$result->getBody());
    }

    public function testDefaultRouteDirect()
    {
        $route = new ControllersRoute(
            'controllers',
            '/[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $route = $route->withControllers(['m' => 'magic'])->withDefaults(['controller' => 'dummy']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/dummy');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$result->getBody());
    }

    public function testDefaultRouteDirectAnother()
    {
        $route = new ControllersRoute(
            'controllers',
            '/[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $route = $route->withControllers(['m' => 'magic'])->withDefaults(['controller' => 'dummy']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/magic');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(':{"controller":"magic","action":null,"id":null}',
            (string)$result->getBody());
    }

    public function testDefaultRouteAliased()
    {
        $route = new ControllersRoute(
            'controllers',
            '/[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $route = $route->withControllers([
            'm' => MagicController::class
        ])->withDefaults(['controller' => 'dummy']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/m');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(':{"controller":"m","action":null,"id":null}',
            (string)$result->getBody());
    }

    public function testDefaultRouteAliasedParameters()
    {
        $route = new ControllersRoute(
            'controllers',
            '/[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $route = $route->withControllers([
            'm' => MagicController::class
        ])->withDefaults(['controller' => 'dummy']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/m/action/100');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('action:{"controller":"m","action":"action","id":"100"}',
            (string)$result->getBody());
    }

    public function testDefaultRouteAliasedParametersWithRestfulCore()
    {
        $route = new ControllersRoute(
            'controllers',
            '/[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $route = $route->withControllers([
            'm' => MagicController::class
        ])->withDefaults(['controller' => 'dummy']);

        $this->http->defaultRoute($route->withCore(RESTfulCore::class));

        //Default
        $result = $this->get('/m/action/100');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('getAction:{"controller":"m","action":"action","id":"100"}',
            (string)$result->getBody());
    }

    public function testControllersRouteExplicitDefault()
    {
        $route = new ControllersRoute('controllers', '/[<controller>[/<action>[/<id>]]]');

        $route = $route->withControllers([
            'm' => MagicController::class,
            'i' => DummyController::class
        ])->withDefaults(['controller' => 'i']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$result->getBody());
    }

    public function testControllersRouteExplicit()
    {
        $route = new ControllersRoute('controllers', '/[<controller>[/<action>[/<id>]]]');

        $route = $route->withControllers([
            'm' => MagicController::class,
            'i' => DummyController::class
        ])->withDefaults(['controller' => 'i']);

        $this->http->defaultRoute($route);

        //Default
        $result = $this->get('/i');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$result->getBody());
    }
}