<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Exception\DirectoryException;

/**
 * Manages application directories.
 */
interface DirectoriesInterface
{
    public function has(string $name): bool;

    /**
     * @param non-empty-string $name Directory alias, ie. "framework".
     * @param string $path Directory path without ending slash.
     *
     * @throws DirectoryException
     */
    public function set(string $name, string $path): self;

    /**
     * Get directory value.
     *
     * @param non-empty-string $name Directory alias, ie. "framework".
     * @throws DirectoryException When no directory found.
     */
    public function get(string $name): string;

    /**
     * List all registered directories.
     */
    public function getAll(): array;
}
