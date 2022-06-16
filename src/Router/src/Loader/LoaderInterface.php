<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

interface LoaderInterface
{
    /**
     * Loads a routes.
     *
     * @throws \Exception If something went wrong
     */
    public function load(mixed $resource, string $type = null): mixed;

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed $resource A resource
     */
    public function supports(mixed $resource, string $type = null): bool;
}
