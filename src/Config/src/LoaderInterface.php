<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Config\Exception\LoaderException;

interface LoaderInterface
{
    /**
     * Return true if config section exists.
     *
     * @param string $section
     * @return bool
     */
    public function has(string $section): bool;

    /**
     * @param string $section
     * @return array
     *
     * @throws LoaderException
     */
    public function load(string $section): array;
}
