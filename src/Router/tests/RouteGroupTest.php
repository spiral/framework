<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Core\Container;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Route;
use Spiral\Router\RouteGroup;
use Spiral\Router\Target\AbstractTarget;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Stub\AnotherMiddleware;
use Spiral\Tests\Router\Stub\RoutesTestCore;
use Spiral\Tests\Router\Stub\TestMiddleware;

final class RouteGroupTest extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(LoaderInterface::class, $this->createMock(LoaderInterface::class));
        $this->container->bind(UriFactoryInterface::class, Psr17Factory::class);
    }

    public function testCoreString(): void
    {
        $group = new RouteGroup();

        $group->setCore(RoutesTestCore::class);
        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $group->register($this->router, $this->container);

        $t = $this->getProperty($this->router->getRoute('name'), 'target');

        self::assertInstanceOf(Action::class, $t);

        self::assertSame('controller', $this->getProperty($t, 'controller'));
        self::assertSame('method', $this->getProperty($t, 'action'));

        self::assertInstanceOf(RoutesTestCore::class, $this->getActionProperty($t, 'pipeline'));
    }

    public function testCoreObject(): void
    {
        $group = new RouteGroup();

        $group->setCore(new RoutesTestCore($this->container));
        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $group->register($this->router, $this->container);

        $t = $this->getProperty($this->router->getRoute('name'), 'target');

        self::assertInstanceOf(Action::class, $t);

        self::assertSame('controller', $this->getProperty($t, 'controller'));
        self::assertSame('method', $this->getProperty($t, 'action'));

        self::assertInstanceOf(RoutesTestCore::class, $this->getActionProperty($t, 'pipeline'));
    }

    public function testGroupHasRoute(): void
    {
        $group = new RouteGroup();

        $group->addRoute('foo', new Route('/', new Action('controller', 'method')));
        $group->register($this->router, $this->container);

        self::assertTrue($group->hasRoute('foo'));
        self::assertFalse($group->hasRoute('bar'));
    }

    #[DataProvider('middlewaresDataProvider')]
    public function testMiddleware(mixed $middleware): void
    {
        $group = new RouteGroup();
        $group->addMiddleware($middleware);

        $route = new Route('/', new Action('controller', 'method'));
        $group->addRoute('name', $route->withContainer($this->container));
        $group->register($this->router, $this->container);

        $r = $this->router->getRoute('name');

        $p = $this->getProperty($r, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        self::assertCount(1, $m);
        // Because of the pipeline is lazy
        self::assertSame($middleware, $m[0]);
    }

    public function testRouteWithMiddlewareAddGroupMiddleware(): void
    {
        $group = new RouteGroup();
        $group->addMiddleware(TestMiddleware::class);

        $route = new Route('/', new Action('controller', 'method'));
        $group->addRoute('name', $route->withContainer($this->container)->withMiddleware(AnotherMiddleware::class));
        $group->register($this->router, $this->container);

        $r = $this->router->getRoute('name');

        $p = $this->getProperty($r, 'pipeline');
        $m = $this->getProperty($p, 'middleware');

        self::assertCount(2, $m);

        // Because of the pipeline is lazy
        self::assertSame(TestMiddleware::class, $m[1]);
        self::assertSame(AnotherMiddleware::class, $m[0]);
    }

    public function testWithoutNamePrefix(): void
    {
        $group = new RouteGroup();
        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $group->register($this->router, $this->container);

        self::assertTrue($group->hasRoute('name'));
    }

    public function testWithNamePrefix(): void
    {
        $group = new RouteGroup();
        $group->setNamePrefix('admin.');
        $group->addRoute('name', new Route('/', new Action('controller', 'method')));
        $group->register($this->router, $this->container);

        self::assertTrue($group->hasRoute('admin.name'));
        self::assertFalse($group->hasRoute('name'));
    }

    #[DataProvider('routePrefixDataProvider')]
    public function testWithPrefix(string $prefix, string $pattern): void
    {
        $route = new Route($pattern, new Action('controller', 'method'));
        $group = new RouteGroup();
        $group->setPrefix($prefix);
        $group->addRoute('name', $route->withVerbs('GET'));
        $group->register($this->router, $this->container);

        $route = $this->router->getRoute('name');
        self::assertNotNull($route->match(new ServerRequest('GET', '/api/blog')));

        self::assertSame('/api/blog', (string) $route->uri());
    }

    public static function routePrefixDataProvider(): iterable
    {
        yield ['/api/', '/blog'];
        yield ['/api', '/blog'];
        yield ['/api', 'blog'];
        yield ['api/', '/blog'];
        yield ['api', '/blog'];
        yield ['api', 'blog'];
        yield ['api/', '/blog/'];
        yield ['api', '/blog/'];
        yield ['api', 'blog/'];
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
