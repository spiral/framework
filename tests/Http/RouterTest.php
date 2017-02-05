<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Http\Routing;

use Spiral\Core\Containers\SpiralContainer;
use Spiral\Http\Routing\Route;
use Spiral\Http\Routing\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRoutes()
    {
        $router = new Router(new SpiralContainer(), '/prefix');
        $router->addRoute(new Route('test', '/', 'abc'));

        $this->assertCount(1, $router->getRoutes());
    }

    public function testGetRoute()
    {
        $router = new Router(new SpiralContainer(), '/prefix');
        $router->addRoute(new Route('test', '/', 'abc'));

        $this->assertEquals('test', $router->getRoute('test')->getName());
    }

    public function testPrefix()
    {
        $router = new Router(new SpiralContainer(), '/prefix');
        $router->addRoute(new Route('test', '/', 'abc'));

        $this->assertEquals('/prefix/', $router->getRoute('test')->getPrefix());
    }

    public function testDefaultPrefix()
    {
        $router = new Router(new SpiralContainer(), '/');
        $router->addRoute(new Route('test', '/', 'abc'));

        $this->assertEquals('/', $router->getRoute('test')->getPrefix());
    }
}