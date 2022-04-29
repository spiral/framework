<?php

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Config\Exception\PatchException;

interface PatchInterface
{
    /**
     * Patches loaded config file with new values and/or sections. Multiple modifiers can be
     * applied at once.
     *
     * @throws PatchException
     */
    public function patch(array $config): array;
}
