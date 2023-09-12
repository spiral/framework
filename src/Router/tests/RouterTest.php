<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\ServerRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Router\Event\RouteMatched;
use Spiral\Router\Event\RouteNotFound;
use Spiral\Router\Event\Routing;
use Spiral\Router\Exception\RouteNotFoundException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Route;
use Spiral\Router\RouteCollection;

class RouterTest extends BaseTestCase
{
    public function testGetRoutes(): void
    {
        $router = $this->makeRouter();

        $router->setRoute('name', new Route('/', Call::class));
        $this->assertCount(1, $router->getRoutes());
    }

    public function testDefault(): void
    {
        $router = $this->makeRouter();

        $router->setRoute('name', new Route('/', Call::class));
        $router->setDefault(new Route('/', Call::class));

        $this->assertCount(2, $router->getRoutes());
    }

    public function testCastError(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->uri('name/?broken');
    }

    public function testEventsShouldBeDispatched(): void
    {
        $request = new ServerRequest('GET', '/foo');
        $route = (new Route('/foo', Call::class))->withContainer($this->container);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->callback(static fn (Routing|RouteMatched $event): bool => $event->request instanceof ServerRequest));

        $router = $this->makeRouter('', $dispatcher);
        $router->setDefault($route);
        $router->handle($request);
    }

    public function testRouteNotFoundEventShouldBeDispatched(): void
    {
        $request = new ServerRequest('GET', '/foo');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new Routing($request),
                new RouteNotFound($request)
            ));

        $router = $this->makeRouter('', $dispatcher);

        $this->expectException(RouteNotFoundException::class);
        $router->handle($request);
    }

    public function testImportWithHost(): void
    {
        $router = $this->makeRouter('https://host.com', $this->createMock(EventDispatcherInterface::class));

        $configurator = new RoutingConfigurator(new RouteCollection(), $this->createMock(LoaderInterface::class));
        $configurator->add('foo', '//<host>/register')->callable(fn () => null);

        $router->import($configurator);
        $this->container->get(GroupRegistry::class)->registerRoutes($router);

        $uri = (string) $router->uri('foo', ['host' => 'some']);
        $this->assertSame('some/register', $uri);
        $this->assertFalse(\str_contains('https://host.com', $uri));
    }
}
