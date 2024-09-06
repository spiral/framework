<?php

declare(strict_types=1);

namespace Spiral\Config\Patch;

use Spiral\Config\PatchInterface;

final class Group implements PatchInterface
{
    /** @var PatchInterface[] */
    private readonly array $patches;

    public function __construct(PatchInterface ...$patch)
    {
        $this->patches = $patch;
    }

    public function patch(array $config): array
    {
        foreach ($this->patches as $patch) {
            $config = $patch->patch($config);
        }

        return $config;
    }
}
