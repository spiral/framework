<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Traversable;

/**
 * @internal
 */
final class DirectoriesRepository implements DirectoriesRepositoryInterface
{
    /** @var string[] */
    private array $directories;

    /**
     * @param string[] $directories
     */
    public function __construct(array $directories)
    {
        $this->setDirectories($directories);
    }

    public function setDirectories(array $directories): void
    {
        $this->directories = \array_map(
            static fn(string $dir): string => \rtrim($dir, '/'),
            $directories,
        );
    }

    public function addDirectory(string $directory): void
    {
        $this->directories[] = \rtrim($directory, '/');
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->directories);
    }
}