<?php

declare(strict_types=1);

namespace Framework\Bootloader\Router;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\Loader\DelegatingLoader;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\LoaderRegistryInterface;
use Spiral\Router\Registry\DefaultPatternRegistry;
use Spiral\Router\Registry\RoutePatternRegistryInterface;
use Spiral\Router\RouteInterface;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Tests\Framework\BaseTest;

final class RouterBootloaderTest extends BaseTest
{
    public function testCoreInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(CoreInterface::class, CoreInterface::class);
    }

    public function testRouterInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(RouterInterface::class, Router::class);
    }

    public function testRequestHandlerInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(RequestHandlerInterface::class, RouterInterface::class);
    }

    public function testLoaderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(LoaderInterface::class, DelegatingLoader::class);
    }

    public function testLoaderRegistryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(LoaderRegistryInterface::class, LoaderRegistry::class);
    }

    public function testGroupRegistryBinding(): void
    {
        $this->assertContainerBoundAsSingleton(GroupRegistry::class, GroupRegistry::class);
    }

    public function testRoutingConfiguratorBinding(): void
    {
        $this->assertContainerBoundAsSingleton(RoutingConfigurator::class, RoutingConfigurator::class);
    }

    public function testRoutePatternRegistryBinding(): void
    {
        $this->assertContainerBoundAsSingleton(RoutePatternRegistryInterface::class, DefaultPatternRegistry::class);
    }

    public function testRouteInterfaceBinding(): void
    {
        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')
            ->once()
            ->with(Router::ROUTE_ATTRIBUTE, null)
            ->andReturn(\Mockery::mock(RouteInterface::class));

        $this->assertContainerBoundAsSingleton(RouteInterface::class, RouteInterface::class);
    }

    public function testRouteInterfaceShouldThrowAnExceptionWhenRequestDoesNotContainIt(): void
    {
        $this->expectExceptionMessage('Unable to resolve Route, invalid request scope');

        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')
            ->once()
            ->with(Router::ROUTE_ATTRIBUTE, null)
            ->andReturnNull();

        $this->getContainer()->get(RouteInterface::class);
    }
}
