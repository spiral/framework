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
     * Automatically force file extensions, must not alter existed loader.
     *
     * @param string|null $extension
     *
     * @return self
     * @throws LoaderException
     */
    public function withExtension(string $extension = null): LoaderInterface;

    /**
     * Get list of all available namespaces for this specific loader.
     *
     * @return array
     */
    public function getNamespaces(): array;

    /**
     * Fetch namespace encoded in a path.
     *
     * @param string $path
     *
     * @return string
     */
    public function fetchNamespace(string $path): string;

    /**
     * Fetch view name related to path (must not include namespace or extension).
     *
     * @param string $path
     *
     * @return string
     */
    public function fetchName(string $path): string;
}