<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Cocur\Slugify\Slugify;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
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
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;
use Spiral\Tests\Router\Diactoros\ResponseFactory;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Stub\TestLoader;
use Spiral\Tests\Router\Stub\TestMiddleware;

abstract class BaseTest extends TestCase
{
    protected Container $container;
    protected Router $router;

    protected function setUp(): void
    {
        $this->initContainer();
        $this->initRouter();
    }

    protected function makeRouter(string $basePath = '', ?EventDispatcherInterface $dispatcher = null): RouterInterface
    {
        return new Router(
            $basePath,
            new UriHandler(
                new UriFactory(),
                new Slugify()
            ),
            $this->container,
            $dispatcher,
            new NullTracer($this->container)
        );
    }

    /**
     * @throws \ReflectionException
     */
    protected function getProperty(object $object, string $property): mixed
    {
        $r = new \ReflectionObject($object);

        return $r->getProperty($property)->getValue($object);
    }

    public static function middlewaresDataProvider(): \Traversable
    {
        yield [TestMiddleware::class];
        yield [new TestMiddleware()];
        yield [new Autowire(TestMiddleware::class)];
    }

    private function initContainer(): void
    {
        $this->container = new Container();
        $this->container->bind(TracerInterface::class, new NullTracer($this->container));
        $this->container->bind(ResponseFactoryInterface::class, new ResponseFactory(new HttpConfig(['headers' => []])));
        $this->container->bind(UriFactoryInterface::class, new UriFactory());
        $this->container->bind(
            LoaderInterface::class,
            new DelegatingLoader(
                new LoaderRegistry([
                    new PhpFileLoader($this->container, $this->container),
                    new TestLoader(),
                ])
            )
        );

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
