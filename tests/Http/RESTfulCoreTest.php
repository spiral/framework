<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\RESTfulCore;
use Spiral\Http\Routing\Route;

class RESTfulCoreTest extends HttpTest
{
    public function testRestfulRouteGet()
    {
        $route = new Route(
            'resful',
            '/[<action>]',
            "TestApplication\\Controllers\\MagicController::<action>"
        );

        $route = $route->withCore(RESTfulCore::class);

        $this->http->addRoute($route);

        $result = $this->get('/');

        $this->assertSame("get:{\"action\":null}", $result->getBody()->__toString());
    }

    public function testRestfulRouteGetWithAction()
    {
        $route = new Route(
            'resful',
            '/[<action>]',
            "TestApplication\\Controllers\\MagicController::<action>"
        );

        $route = $route->withCore(RESTfulCore::class);

        $this->http->addRoute($route);

        $result = $this->get('/post');

        $this->assertSame("getPost:{\"action\":\"post\"}", $result->getBody()->__toString());
    }

    public function testRestfulRoutePost()
    {
        $route = new Route(
            'resful',
            '/[<action>]',
            "TestApplication\\Controllers\\MagicController::<action>"
        );

        $route = $route->withCore(RESTfulCore::class);

        $this->http->addRoute($route);

        $result = $this->post('/');

        $this->assertSame("post:{\"action\":null}", $result->getBody()->__toString());
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\RESTfulException
     */
    public function testExceptions()
    {
        $core = new RESTfulCore($this->app);

        $core->callAction('test');
    }
}