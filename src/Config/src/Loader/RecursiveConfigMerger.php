<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

final class RecursiveConfigMerger implements ConfigsMergerInterface
{
    public function merge(array ...$config): array
    {
        return \array_merge_recursive(...$config);
    }
}