<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader\Configurator;

use Spiral\Router\Loader\Configurator\RouteConfigurator;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\RouteCollection;
use Spiral\Tests\Router\BaseTestCase;

final class RoutingConfiguratorTest extends BaseTestCase
{
    public function testImportWithoutConcreteLoader(): void
    {
        $routes = $this->container->get(RoutingConfigurator::class);

        $this->assertCount(0, $routes->getCollection());

        $routes->import(\dirname(__DIR__, 2) . '/Fixtures/file.php');

        $this->assertCount(3, $routes->getCollection());
    }

    public function testImportWithLoader(): void
    {
        $routes = $this->container->get(RoutingConfigurator::class);

        $this->assertCount(0, $routes->getCollection());

        $routes->import(\dirname(__DIR__, 2) . '/Fixtures/file.php', 'php');

        $this->assertCount(3, $routes->getCollection());
    }

    public function testImportWithWrongLoader(): void
    {
        $routes = $this->container->get(RoutingConfigurator::class);

        $this->assertCount(0, $routes->getCollection());

        $routes->import(\dirname(__DIR__, 2) . '/Fixtures/file.php', 'yaml');

        $this->assertCount(0, $routes->getCollection());
    }

    public function testGetCollection(): void
    {
        $routes = $this->container->get(RoutingConfigurator::class);

        $this->assertInstanceOf(RouteCollection::class, $routes->getCollection());
    }

    public function testDefault(): void
    {
        $routes = $this->container->get(RoutingConfigurator::class);

        $this->assertNull($routes->getDefault());

        $routes->default('/')->callable(static fn () => null);

        $this->assertInstanceOf(RouteConfigurator::class, $routes->getDefault());
    }

    public function testAdd(): void
    {
        $routes = $this->container->get(RoutingConfigurator::class);

        $this->assertCount(0, $routes->getCollection());
        $route = $routes->add('test', '/')->callable(static fn () => null);
        $this->assertInstanceOf(RouteConfigurator::class, $route);

        // important. For destruct
        unset($route);
        $this->assertCount(1, $routes->getCollection());
    }
}
