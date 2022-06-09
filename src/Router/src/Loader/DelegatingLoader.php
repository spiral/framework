<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

use Spiral\Router\Exception\LoaderLoadException;

final class DelegatingLoader implements LoaderInterface
{
    public function __construct(
        private readonly LoaderRegistryInterface $registry
    ) {
    }

    public function load(mixed $resource, string $type = null): mixed
    {
        if ($loader = $this->registry->resolve($resource, $type) === false) {
            throw new LoaderLoadException(\sprintf('Loader for type [%s] not found.', $type));
        }

        return $loader->load($resource, $type);
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return false !== $this->registry->resolve($resource, $type);
    }
}
