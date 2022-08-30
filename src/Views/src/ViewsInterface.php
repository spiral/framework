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
     *
     * @throws ViewException
     */
    public function get(string $path): ViewInterface;

    /**
     * Compile one of multiple cache versions for a given view path.
     *
     *
     * @throws ViewException
     */
    public function compile(string $path);

    /**
     * Reset view cache for a given path.
     *
     *
     * @throws ViewException
     */
    public function reset(string $path);

    /**
     * Render template.
     */
    public function render(string $path, array $data = []): string;
}
