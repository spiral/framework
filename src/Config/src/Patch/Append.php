<?php

declare(strict_types=1);

namespace Spiral\Config\Patch;

use Spiral\Config\Exception\DotNotFoundException;
use Spiral\Config\Exception\PatchException;
use Spiral\Config\Patch\Traits\DotTrait;
use Spiral\Config\PatchInterface;

final class Append implements PatchInterface
{
    use DotTrait;

    private string $position;

    public function __construct(
        string $position,
        private ?string $key,
        private mixed $value
    ) {
        $this->position = $position === '.' ? '' : $position;
    }

    public function patch(array $config): array
    {
        try {
            $target = &$this->dotGet($config, $this->position);

            if ($this->key !== null) {
                $target[$this->key] = $this->value;
            } else {
                $target[] = $this->value;
            }
        } catch (DotNotFoundException $e) {
            throw new PatchException($e->getMessage(), $e->getCode(), $e);
        }

        return $config;
    }
}
