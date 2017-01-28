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
interface LoaderInterface
{
    /**
     * Get list of all available namespaces for this specific loader.
     *
     * @return array
     */
    public function getNamespaces(): array;

    /**
     * Automatically force file extensions, must not alter existed loader.
     *
     * @param string|null $extension
     *
     * @return self
     * @throws LoaderException
     */
    public function withExtension(string $extension = null): LoaderInterface;

    /**
     * Get source for given name.
     *
     * @param string $path
     *
     * @return ViewSource
     *
     * @throws LoaderException
     */
    public function getSourceContext(string $path): ViewSource;

    /**
     * Given path exists in loader.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool;
}