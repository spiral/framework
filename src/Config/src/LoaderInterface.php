<?php

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Config\Exception\LoaderException;

interface LoaderInterface
{
    /**
     * Return true if config section exists.
     */
    public function has(string $section): bool;

    /**
     *
     * @throws LoaderException
     */
    public function load(string $section): array;
}
