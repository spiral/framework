<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Config\Exception\LoaderException;

interface FileLoaderInterface
{
    /**
     * Load file content.
     *
     * @throws LoaderException
     */
    public function loadFile(string $section, string $filename): array;
}
