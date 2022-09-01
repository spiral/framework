<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Psr\Container\ContainerInterface;
use Spiral\Config\Exception\LoaderException;
use Spiral\Core\ContainerScope;

/**
 * Loads PHP files inside container scope.
 */
final class PhpLoader implements FileLoaderInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function loadFile(string $section, string $filename): array
    {
        try {
            return ContainerScope::runScope($this->container, static fn () => require $filename);
        } catch (\Throwable $e) {
            throw new LoaderException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
