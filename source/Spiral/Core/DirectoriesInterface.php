<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

/**
 * Manages application directories.
 */
interface DirectoriesInterface
{
    /**
     * Set application directory.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @param string $path  Directory path without ending slash.
     * @return $this
     */
    public function setDirectory($alias, $path);

    /**
     * Get application directory.
     *
     * @param string $alias
     * @return string
     */
    public function directory($alias);

    /**
     * All application directories.
     *
     * @return array
     */
    public function getDirectories();
}