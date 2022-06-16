<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

class LoaderRegistry implements LoaderRegistryInterface
{
    /**
     * @var LoaderInterface[] An array of LoaderInterface objects
     */
    private array $loaders = [];

    /**
     * @param LoaderInterface[] $loaders An array of loaders
     */
    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    public function resolve(mixed $resource, string $type = null): LoaderInterface|false
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource, $type)) {
                return $loader;
            }
        }

        return false;
    }

    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * Returns the registered loaders.
     *
     * @return LoaderInterface[]
     */
    public function getLoaders(): array
    {
        return $this->loaders;
    }
}
