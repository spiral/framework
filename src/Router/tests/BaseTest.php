<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Cocur\Slugify\Slugify;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\Loader\DelegatingLoader;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Tests\Router\Diactoros\ResponseFactory;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Stub\TestLoader;

abstract class BaseTest extends TestCase
{
    protected Container $container;
    protected Router $router;

    public function setUp(): void
    {
        $this->initContainer();
        $this->initRouter();
    }

    protected function makeRouter(string $basePath = ''): RouterInterface
    {
        return new Router($basePath, new UriHandler(
            new UriFactory(),
            new Slugify()
        ), $this->container);
    }

    private function initContainer(): void
    {
        $this->container = new Container();
        $this->container->bind(ResponseFactoryInterface::class, new ResponseFactory(new HttpConfig(['headers' => []])));
        $this->container->bind(UriFactoryInterface::class, new UriFactory());
        $this->container->bind(LoaderInterface::class, new DelegatingLoader(new LoaderRegistry([
            new PhpFileLoader($this->container),
            new TestLoader()
        ])));

        $this->container->bind(CoreInterface::class, Core::class);
        $this->container->bindSingleton(GroupRegistry::class, GroupRegistry::class);
        $this->container->bindSingleton(RoutingConfigurator::class, RoutingConfigurator::class);
    }

    private function initRouter(): void
    {
        $this->router = $this->makeRouter();
        $this->container->bindSingleton(RouterInterface::class, $this->router);
    }
}
