<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

use Spiral\Views\Exceptions\LoaderException;

/**
 * View loader interface. Compatible with twig loader.
 */
interface LoaderInterface extends \Twig_LoaderInterface, \Spiral\Stempler\LoaderInterface
{
    /**
     * @return array
     */
    public function getNamespaces();

    /**
     * Get namespace related to path.
     *
     * @param string $path
     * @return string
     */
    public function viewNamespace($path);

    /**
     * Get view name related to path (must not include namespace or extension).
     *
     * @param string $path
     * @return string
     */
    public function viewName($path);

    /**
     * Automatically force file extensions, must not alter existed loader.
     *
     * @param string|null $extension
     * @return self
     * @throws LoaderException
     */
    public function withExtension($extension);
}