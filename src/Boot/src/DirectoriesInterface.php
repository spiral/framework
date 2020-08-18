<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Exception\DirectoryException;

/**
 * Manages application directories.
 */
interface DirectoriesInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @param string $name Directory alias, ie. "framework".
     * @param string $path Directory path without ending slash.
     *
     * @throws DirectoryException
     */
    public function set(string $name, string $path);

    /**
     * Get directory value.
     *
     * @param string $name
     * @return string
     *
     * @throws DirectoryException When no directory found.
     */
    public function get(string $name): string;

    /**
     * List all registered directories.
     *
     * @return array
     */
    public function getAll(): array;
}
