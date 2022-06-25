<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Http\Pipeline;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\PipelineFactory;

abstract class RoutesBootloader extends Bootloader
{
    public function init(HttpBootloader $http): void
    {
        foreach ($this->globalMiddleware() as $middleware) {
            $http->addMiddleware($middleware);
        }
    }

    public function boot(RoutingConfigurator $routes, Container $container, GroupRegistry $groups): void
    {
        $middlewareGroups = $this->middlewareGroups();

        $this->registerMiddlewareGroups($container, $middlewareGroups);
        $this->registerMiddlewareForRouteGroups($groups, $middlewareGroups);

        $this->defineRoutes($routes);
    }

    /**
     * Override this method to configure application routes
     */
    protected function defineRoutes(RoutingConfigurator $routes): void
    {
    }

    /**
     * @return array<string,array<MiddlewareInterface|class-string<MiddlewareInterface>>>
     */
    abstract protected function globalMiddleware(): array;

    /**
     * @return array<string,array<MiddlewareInterface|class-string<MiddlewareInterface>>>
     */
    abstract protected function middlewareGroups(): array;

    private function registerMiddlewareForRouteGroups(GroupRegistry $registry, array $groups): void
    {
        foreach ($groups as $group => $middleware) {
            $registry->getGroup($group)->addMiddleware('middleware:'.$group);
        }
    }

    private function registerMiddlewareGroups(Container $container, array $groups): void
    {
        foreach ($groups as $group => $middleware) {
            $container->bind(
                'middleware:'.$group,
                static function (PipelineFactory $factory) use ($middleware): Pipeline {
                    return $factory->createWithMiddleware($middleware);
                }
            );
        }
    }
}
