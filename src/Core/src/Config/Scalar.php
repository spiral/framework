<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

final class Scalar extends Binding
{
    public function __construct(
        public readonly bool|int|string|float $value,
    ) {
    }
}
