<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Config\Exception\LoaderException;

interface FileLoaderInterface
{
    /**
     * Load file content.
     *
     * @param string $section
     * @param string $filename
     * @return array
     *
     * @throws LoaderException
     */
    public function loadFile(string $section, string $filename): array;
}
