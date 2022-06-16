<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Core\Container;
use Spiral\Http\Pipeline;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Route;
use Spiral\Router\RouteGroup;
use Spiral\Router\Router;
use Spiral\Router\Target\AbstractTarget;
use Spiral\Router\Target\Action;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Stub\RoutesTestCore;
use Spiral\Tests\Router\Stub\TestMiddleware;

class RoutesGroupTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(LoaderInterface::class, $this->createMock(LoaderInterface::class));
    }

    public function testCoreString(): void
    {
        $handler = new UriHandler(new Psr17Factory());
        $router = new Router('/', $handler, $this->container);
        $group = new RouteGroup($this->container, $router, new Pipeline($this->container), $handler);

        $group->setCore(RoutesTestCore::class);

        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $t = $this->getProperty($router->getRoute('name'), 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertInstanceOf(RoutesTestCore::class, $this->getActionProperty($t, 'core'));
    }

    public function testCoreObject(): void
    {
        $handler = new UriHandler(new Psr17Factory());
        $router = new Router('/', $handler, $this->container);
        $group = new RouteGroup($this->container, $router, new Pipeline($this->container), $handler);

        $group->setCore(new RoutesTestCore($this->container));

        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $t = $this->getProperty($router->getRoute('name'), 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertInstanceOf(RoutesTestCore::class, $this->getActionProperty($t, 'core'));
    }

    public function testMiddleware(): void
    {
        $handler = new UriHandler(new Psr17Factory());
        $router = new Router('/', $handler, $this->container);
        $group = new RouteGroup($this->container, $router, new Pipeline($this->container), $handler);
        $group->addMiddleware(TestMiddleware::class);

        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $r = $router->getRoute('name');

        $p = $this->getProperty($r, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        $this->assertCount(1, $m);
        $this->assertInstanceOf(TestMiddleware::class, $m[0]);
    }

    /**
     * @throws \ReflectionException
     */
    private function getProperty(object $object, string $property): mixed
    {
        $r = new \ReflectionObject($object);

        return $r->getProperty($property)->getValue($object);
    }

    /**
     * @throws \ReflectionException
     */
    private function getActionProperty(object $object, string $property): mixed
    {
        $r = new \ReflectionClass(AbstractTarget::class);

        return $r->getProperty($property)->getValue($object);
    }
}
