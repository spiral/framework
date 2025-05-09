<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

interface ConfigsMergerInterface
{
    /**
     * @param array ...$config
     * @return array
     */
    public function merge(array ...$config): array;
}