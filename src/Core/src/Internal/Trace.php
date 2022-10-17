<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
final class Trace implements \Stringable
{
    public function __construct(
        public readonly string $alias,
        public array $info,
    ) {
    }

    public function __toString(): string
    {
        return $this->alias . '  ' . \json_encode($this->info, JSON_PRETTY_PRINT);
    }
}
