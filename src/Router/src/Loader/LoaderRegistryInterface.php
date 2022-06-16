<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

interface LoaderRegistryInterface
{
    /**
     * Returns a loader able to load the resource.
     *
     * @param string|null $type The resource type or null if unknown
     */
    public function resolve(mixed $resource, string $type = null): LoaderInterface|false;
}
