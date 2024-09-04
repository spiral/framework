<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Router\Exception\LoaderLoadException;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\RouteCollection;

final class PhpFileLoader implements LoaderInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly ResolverInterface $resolver,
    ) {
    }

    /**
     * Loads a PHP file.
     */
    public function load(mixed $resource, string $type = null): RouteCollection
    {
        if (!\file_exists($resource)) {
            throw new LoaderLoadException(\sprintf('File [%s] does not exist.', $resource));
        }

        $load = static fn (string $path) => include $path;

        $callback = $load($resource);

        $collection = new RouteCollection();

        $configurator = new RoutingConfigurator($collection, $this->factory->make(LoaderInterface::class));

        $args = $this->resolver->resolveArguments(new \ReflectionFunction($callback), [$configurator]);

        // Compiling routes from callback
        $callback(...$args);

        return $collection;
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return
            \is_string($resource) &&
            \pathinfo($resource, \PATHINFO_EXTENSION) === 'php' &&
            (!$type || $type === 'php');
    }
}
