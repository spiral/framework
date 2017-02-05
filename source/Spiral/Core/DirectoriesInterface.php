<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\DirectoryException;

/**
 * Manages application directories.
 */
interface DirectoriesInterface
{
    /**
     * @param string $alias
     *
     * @return bool
     */
    public function hasDirectory(string $alias): bool;

    /**
     * @param string $alias Directory alias, ie. "framework".
     * @param string $path  Directory path without ending slash.
     *
     * @return self|$this
     *
     * @throws DirectoryException
     */
    public function setDirectory(string $alias, string $path): self;

    /**
     * Get directory value.
     *
     * @param string $alias
     *
     * @return string
     *
     * @throws DirectoryException When no directory found.
     */
    public function directory(string $alias): string;

    /**
     * @return array
     */
    public function getDirectories(): array;
}