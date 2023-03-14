<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\Autowire;
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

    public function boot(RoutingConfigurator $routes, BinderInterface $binder, GroupRegistry $groups): void
    {
        $middlewareGroups = $this->middlewareGroups();

        $this->registerMiddlewareGroups($binder, $middlewareGroups);
        $this->registerMiddlewareForRouteGroups($groups, \array_keys($middlewareGroups));

        $this->configureRouteGroups($groups);
        $this->defineRoutes($routes);
    }

    /**
     * Override this method to configure application routes
     */
    protected function defineRoutes(RoutingConfigurator $routes): void
    {
    }

    /**
     * Override this method to configure route groups
     */
    protected function configureRouteGroups(GroupRegistry $groups): void
    {
    }

    /**
     * @return array<MiddlewareInterface|class-string<MiddlewareInterface>>
     */
    abstract protected function globalMiddleware(): array;

    /**
     * @return array<string,array<MiddlewareInterface|class-string<MiddlewareInterface>|Autowire>>
     */
    abstract protected function middlewareGroups(): array;

    private function registerMiddlewareGroups(BinderInterface $binder, array $groups): void
    {
        foreach ($groups as $group => $middleware) {
            $binder->bind(
                'middleware:' . $group,
                static function (PipelineFactory $factory) use ($middleware): Pipeline {
                    return $factory->createWithMiddleware($middleware);
                }
            );
        }
    }

    private function registerMiddlewareForRouteGroups(GroupRegistry $registry, array $groups): void
    {
        foreach ($groups as $group) {
            $registry->getGroup($group)->addMiddleware('middleware:' . $group);
        }
    }
}
