<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Core\Attribute\Singleton;

/**
 * @internal
 */
#[Singleton]
final class FileLoaderRegistry
{
    public function __construct(
        /**@var array<non-empty-string, FileLoaderInterface> */
        private array $loaders = [],
    ) {}

    /**
     * @param non-empty-string $ext
     */
    public function register(string $ext, FileLoaderInterface $loader): void
    {
        if (!isset($this->loaders[$ext]) || $this->loaders[$ext]::class !== $loader::class) {
            $this->loaders[$ext] = $loader;
        }
    }

    public function getExtensions(): array
    {
        return \array_keys($this->loaders);
    }

    public function getLoader(string $ext): FileLoaderInterface
    {
        return $this->loaders[$ext];
    }
}