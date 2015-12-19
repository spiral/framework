<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\UndefinedAliasException;

/**
 * Manages application directories.
 */
interface DirectoriesInterface
{
    /**
     * @param string $alias
     * @return bool
     */
    public function hasDirectory($alias);

    /**
     * @param string $alias Directory alias, ie. "framework".
     * @param string $path  Directory path without ending slash.
     * @return $this
     * @throws UndefinedAliasException
     */
    public function setDirectory($alias, $path);

    /**
     * @param string $alias
     * @return string
     */
    public function directory($alias);

    /**
     * @return array
     */
    public function getDirectories();
}