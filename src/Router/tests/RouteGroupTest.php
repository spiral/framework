<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Core\Container;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Route;
use Spiral\Router\RouteGroup;
use Spiral\Router\Target\AbstractTarget;
use Spiral\Router\Target\Action;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Stub\AnotherMiddleware;
use Spiral\Tests\Router\Stub\RoutesTestCore;
use Spiral\Tests\Router\Stub\TestMiddleware;

final class RouteGroupTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(LoaderInterface::class, $this->createMock(LoaderInterface::class));
    }

    public function testCoreString(): void
    {
        $group = $this->createRouteGroup();

        $group->setCore(RoutesTestCore::class);

        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $t = $this->getProperty($this->router->getRoute('name'), 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertInstanceOf(RoutesTestCore::class, $this->getActionProperty($t, 'core'));
    }

    public function testCoreObject(): void
    {
        $group = $this->createRouteGroup();

        $group->setCore(new RoutesTestCore($this->container));

        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $t = $this->getProperty($this->router->getRoute('name'), 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertInstanceOf(RoutesTestCore::class, $this->getActionProperty($t, 'core'));
    }

    public function testGroupHasRoute(): void
    {
        $group = $this->createRouteGroup();

        $group->addRoute('foo', new Route('/', new Action('controller', 'method')));
        $this->assertTrue($group->hasRoute('foo'));
        $this->assertFalse($group->hasRoute('bar'));
    }

    /** @dataProvider middlewaresDataProvider */
    public function testMiddleware(mixed $middleware): void
    {
        $group = $this->createRouteGroup();
        $group->addMiddleware($middleware);

        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $r = $this->router->getRoute('name');

        $p = $this->getProperty($r, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        $this->assertCount(1, $m);
        $this->assertInstanceOf(TestMiddleware::class, $m[0]);
    }

    public function testRouteWithMiddlewareAddGroupMiddleware(): void
    {
        $group = $this->createRouteGroup();
        $group->addMiddleware(TestMiddleware::class);

        $route = new Route('/', new Action('controller', 'method'));
        $route = $route->withMiddleware(AnotherMiddleware::class);

        $group->addRoute('name', $route);
        $r = $this->router->getRoute('name');

        $p = $this->getProperty($r, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        $this->assertCount(2, $m);

        $this->assertInstanceOf(TestMiddleware::class, $m[1]);
        $this->assertInstanceOf(AnotherMiddleware::class, $m[0]);
    }

    public function testWithoutNamePrefix(): void
    {
        $group = $this->createRouteGroup();
        $group->addRoute('name', new Route('/', new Action('controller', 'method')));

        $this->assertTrue($group->hasRoute('name'));
    }

    public function testWithNamePrefix(): void
    {
        $group = $this->createRouteGroup();
        $group->setNamePrefix('admin.');
        $group->addRoute('name', new Route('/', new Action('controller', 'method')));

        $this->assertTrue($group->hasRoute('admin.name'));
        $this->assertFalse($group->hasRoute('name'));
    }

    /**
     * @throws \ReflectionException
     */
    private function getActionProperty(object $object, string $property): mixed
    {
        $r = new \ReflectionClass(AbstractTarget::class);

        return $r->getProperty($property)->getValue($object);
    }

    private function createRouteGroup(): RouteGroup
    {
        $handler = new UriHandler(new Psr17Factory());

        return new RouteGroup($this->container, $this->router, $handler);
    }
}
