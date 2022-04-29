<?php

declare(strict_types=1);

namespace Spiral\Config\Patch;

use Spiral\Config\Exception\DotNotFoundException;
use Spiral\Config\Patch\Traits\DotTrait;
use Spiral\Config\PatchInterface;

final class Delete implements PatchInterface
{
    use DotTrait;

    private string $position;

    public function __construct(
        string $position,
        private readonly ?string $key,
        private mixed $value = null
    ) {
        $this->position = $position === '.' ? '' : $position;
    }

    public function patch(array $config): array
    {
        try {
            $target = &$this->dotGet($config, $this->position);

            if ($this->key !== null) {
                unset($target[$this->key]);
            } else {
                foreach ($target as $key => $value) {
                    if ($value === $this->value) {
                        unset($target[$key]);
                        break;
                    }
                }
            }
        } catch (DotNotFoundException) {
            // doing nothing when section not found
        }

        return $config;
    }
}
