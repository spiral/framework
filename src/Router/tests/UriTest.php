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
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Fixtures\TestController;

class UriTest extends BaseTest
{
    public function testCastRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->uri('group/test:test');
        $this->assertSame('/test/test', $uri->getPath());
    }

    public function testQuery(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->uri('group/test:id', ['id' => 100, 'data' => 'hello']);
        $this->assertSame('/test/id/100', $uri->getPath());
        $this->assertSame('data=hello', $uri->getQuery());
    }

    public function testDirect(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->getRoute('group')->uri(['test', 'id', 100]);
        $this->assertSame('/test/id/100', $uri->getPath());
    }

    public function testSlug(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->getRoute('group')->uri(['test', 'id', 100, 'Hello World']);
        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testSlugDefault(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->uri('test:id', ['id' => 100, 'title' => 'Hello World']);
        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testSlugNoDefault(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();

        $uri = $router->uri('test:id', ['id' => 100, 'title' => 'Hello World']);
        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testObject(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class,
            ]))
        );

        $uri = $router->uri('test:id', ['id' => 100, 'title' => new class implements \Stringable {
            public function __toString()
            {
                return 'hello-world';
            }
        }]);

        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }
}
