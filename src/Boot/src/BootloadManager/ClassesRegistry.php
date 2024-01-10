<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Boot\BootloadManagerInterface;
use Spiral\Boot\Exception\BootloaderAlreadyBootedException;
use Spiral\Core\Attribute\Singleton;

/**
 * @internal
 * @psalm-import-type TClass from BootloadManagerInterface
 */
#[Singleton]
final class ClassesRegistry
{
    /** @var TClass[] */
    private array $classes = [];

    /**
     * @param TClass $class
     */
    public function register(string $class): void
    {
        if ($this->isBooted($class)) {
            throw new BootloaderAlreadyBootedException($class);
        }

        $this->classes[] = $class;
    }

    /**
     * @param TClass $class
     */
    public function isBooted(string $class): bool
    {
        return \in_array($class, $this->classes, true);
    }

    /**
     * Get bootloaded classes.
     * @return TClass[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
