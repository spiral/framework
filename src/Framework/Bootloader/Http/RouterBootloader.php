<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\BinderInterface;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Framework\Kernel;
use Spiral\Framework\Spiral;
use Spiral\Http\Config\HttpConfig;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\Loader\DelegatingLoader;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\LoaderRegistryInterface;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Router\Registry\DefaultPatternRegistry;
use Spiral\Router\Registry\RoutePatternRegistryInterface;
use Spiral\Router\RouteInterface;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;
use Spiral\Telemetry\Bootloader\TelemetryBootloader;
use Spiral\Telemetry\TracerInterface;

final class RouterBootloader extends Bootloader
{
    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly BinderInterface $binder,
    ) {
    }

    public function defineDependencies(): array
    {
        return [
            HttpBootloader::class,
            TelemetryBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        $this->binder
            ->getBinder(Spiral::HttpRequest)
            ->bindSingleton(RouteInterface::class, [self::class, 'route']);

        return [
            HandlerInterface::class => [self::class, 'handler'],
            CoreInterface::class => Core::class,
            RouterInterface::class => [self::class, 'router'],
            RequestHandlerInterface::class => RouterInterface::class,
            LoaderInterface::class => DelegatingLoader::class,
            LoaderRegistryInterface::class => [self::class, 'initRegistry'],
            GroupRegistry::class => GroupRegistry::class,
            RoutingConfigurator::class => RoutingConfigurator::class,
            RoutePatternRegistryInterface::class => DefaultPatternRegistry::class,
        ];
    }

    public function boot(AbstractKernel $kernel): void
    {
        $configuratorCallback = static function (RouterInterface $router, RoutingConfigurator $routes): void {
            $router->import($routes);
        };
        $groupsCallback = static function (RouterInterface $router, GroupRegistry $groups): void {
            $groups->registerRoutes($router);
        };

        if ($kernel instanceof Kernel) {
            $kernel->appBooted($configuratorCallback);
            $kernel->appBooted($groupsCallback);
        } else {
            $kernel->booted($configuratorCallback);
            $kernel->booted($groupsCallback);
        }
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function router(
        UriHandler $uriHandler,
        #[Proxy] ContainerInterface $container,
        TracerInterface $tracer,
        ?EventDispatcherInterface $dispatcher = null
    ): RouterInterface {
        return new Router(
            $this->config->getConfig(HttpConfig::CONFIG)['basePath'],
            $uriHandler,
            $container,
            $dispatcher,
            $tracer,
        );
    }

    private function handler(?CoreInterface $core, ContainerInterface $container): HandlerInterface
    {
        return $core instanceof HandlerInterface
            ? $core
            : $container->get(Core::class);
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function route(ServerRequestInterface $request): RouteInterface
    {
        $route = $request->getAttribute(Router::ROUTE_ATTRIBUTE, null);
        if ($route === null) {
            throw new ScopeException('Unable to resolve Route, invalid request scope');
        }

        return $route;
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initRegistry(ContainerInterface $container): LoaderRegistryInterface
    {
        return new LoaderRegistry([
            $container->get(PhpFileLoader::class),
        ]);
    }
}
