<?php

declare(strict_types=1);

namespace Spiral\Router\Loader\Configurator;

use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\RouteCollection;

final class RoutingConfigurator
{
    public const DEFAULT_ROUTE_NAME = 'default';

    public function __construct(
        private readonly RouteCollection $collection,
        private readonly LoaderInterface $loader
    ) {
    }

    public function import(string|array $resource, string $type = null): ImportConfigurator
    {
        $imported = $this->loader->load($resource, $type) ?: [];

        if (!\is_array($imported)) {
            return new ImportConfigurator($this->collection, $imported);
        }

        $mergedCollection = new RouteCollection();
        foreach ($imported as $subCollection) {
            $mergedCollection->addCollection($subCollection);
        }

        return new ImportConfigurator($this->collection, $mergedCollection);
    }

    public function getCollection(): RouteCollection
    {
        return $this->collection;
    }

    public function getDefault(): ?RouteConfigurator
    {
        return
            $this->collection->has(self::DEFAULT_ROUTE_NAME) ?
            $this->collection->get(self::DEFAULT_ROUTE_NAME) : null;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $pattern
     */
    public function add(string $name, string $pattern): RouteConfigurator
    {
        return new RouteConfigurator($name, $pattern, $this->collection);
    }

    /**
     * @param non-empty-string $pattern
     */
    public function default(string $pattern): RouteConfigurator
    {
        return new RouteConfigurator(self::DEFAULT_ROUTE_NAME, $pattern, $this->collection);
    }
}
