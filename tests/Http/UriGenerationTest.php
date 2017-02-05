<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\Routing\ControllersRoute;
use Spiral\Http\Routing\Route;

class UriGenerationTest extends HttpTest
{
    public function testStrictUri()
    {
        $router = $this->http->getRouter();
        $router->addRoute(new Route('name', '/pattern', 'target'));

        $this->assertSame('/pattern', (string)$router->uri('name'));
    }

    public function testUriWithOptions()
    {
        $router = $this->http->getRouter();
        $router->addRoute(new Route('name', '/pattern/<action>[/<id>]', 'target'));

        $this->assertSame('/pattern/do', (string)$router->uri('name', [
            'action' => 'do'
        ]));

        $this->assertSame('/pattern/do/10', (string)$router->uri('name', [
            'action' => 'do',
            'id'     => 10
        ]));
    }

    public function testUriWithOptionsAndWithHost()
    {
        $router = $this->http->getRouter();

        $route = new Route(
            'name',
            'http://<username>.website.com/pattern/<action>[/<id>]',
            'target'
        );

        $router->addRoute($route->withHost(true));

        $this->assertSame('http://john.website.com/pattern/do', (string)$router->uri('name', [
            'username' => 'john',
            'action'   => 'do'
        ]));

        $this->assertSame('http://john.website.com/pattern/do/10', (string)$router->uri('name', [
            'username' => 'john',
            'action'   => 'do',
            'id'       => 10
        ]));
    }

    public function testUriWithOptionsAndWithHostAndPort()
    {
        $router = $this->http->getRouter();

        $route = new Route(
            'name',
            'http://<username>.website.com:8080/pattern/<action>[/<id>]',
            'target'
        );

        $router->addRoute($route->withHost(true));

        $this->assertSame('http://john.website.com:8080/pattern/do', (string)$router->uri('name', [
            'username' => 'john',
            'action'   => 'do'
        ]));

        $this->assertSame('http://john.website.com:8080/pattern/do/10',
            (string)$router->uri('name', [
                'username' => 'john',
                'action'   => 'do',
                'id'       => 10
            ]));
    }

    public function testDefaultFallback()
    {
        $router = $this->http->getRouter();

        $route = new ControllersRoute(
            'name',
            '[<controller>[/<action>[/<id>]]]',
            'TestApplication\Controllers'
        );

        $router->defaultRoute($route);

        $this->assertSame('/home', (string)$router->uri('home:'));
        $this->assertSame('/home/action', (string)$router->uri('home:action'));
        $this->assertSame('/home/action/1', (string)$router->uri('home:action', ['id' => 1]));
    }

    public function testStrictUriWithQuery()
    {
        $router = $this->http->getRouter();
        $router->addRoute(new Route('name', '/pattern', 'target'));

        $this->assertSame('/pattern?a=1', (string)$router->uri('name', ['a' => 1]));
    }
}