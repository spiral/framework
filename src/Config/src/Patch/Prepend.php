<?php

declare(strict_types=1);

namespace Spiral\Config\Patch;

use Spiral\Config\Exception\DotNotFoundException;
use Spiral\Config\Exception\PatchException;
use Spiral\Config\Patch\Traits\DotTrait;
use Spiral\Config\PatchInterface;

final class Prepend implements PatchInterface
{
    use DotTrait;

    private string $position;

    public function __construct(
        string $position,
        private readonly ?string $key,
        private mixed $value
    ) {
        $this->position = $position === '.' ? '' : $position;
    }

    public function patch(array $config): array
    {
        try {
            $_target = &$this->dotGet($config, $this->position);

            if ($this->key !== null) {
                $_target = \array_merge([$this->key => $this->value], $_target);
            } else {
                \array_unshift($_target, $this->value);
            }
        } catch (DotNotFoundException $e) {
            throw new PatchException($e->getMessage(), $e->getCode(), $e);
        }

        return $config;
    }
}
