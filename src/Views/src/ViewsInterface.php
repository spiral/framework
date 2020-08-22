<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\ViewException;

interface ViewsInterface
{
    /**
     * Get instance of view class associated with view path (path can include namespace).
     *
     * @param string $path
     * @return ViewInterface
     *
     * @throws ViewException
     */
    public function get(string $path): ViewInterface;

    /**
     * Compile one of multiple cache versions for a given view path.
     *
     * @param string $path
     *
     * @throws ViewException
     */
    public function compile(string $path);

    /**
     * Reset view cache for a given path.
     *
     * @param string $path
     *
     * @throws ViewException
     */
    public function reset(string $path);

    /**
     * Render template.
     *
     * @param string $path
     * @param array  $data
     * @return string
     */
    public function render(string $path, array $data = []): string;
}
