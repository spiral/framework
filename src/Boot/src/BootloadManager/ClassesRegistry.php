<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Boot\Exception\BootloaderAlreadyBootedException;
use Spiral\Core\Container;

/**
 * @internal
 */
final class ClassesRegistry implements Container\SingletonInterface
{
    /** @var array<class-string> */
    private array $classes = [];

    /**
     * @psalm-param class-string $class
     */
    public function register(string $class): void
    {
        if ($this->isBooted($class)) {
            throw new BootloaderAlreadyBootedException($class);
        }

        $this->classes[] = $class;
    }

    /**
     * @psalm-param class-string $class
     */
    public function isBooted(string $class): bool
    {
        return \in_array($class, $this->classes, true);
    }

    /**
     * Get bootloaded classes.
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
