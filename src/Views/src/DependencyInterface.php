<?php

declare(strict_types=1);

namespace Spiral\Views;

/**
 * Represents external value view cache depends on.
 */
interface DependencyInterface
{
    public function getName(): string;

    /**
     * Get current dependency value.
     */
    public function getValue(): mixed;

    /**
     * Return list of all possible dependency values.
     */
    public function getVariants(): array;
}
