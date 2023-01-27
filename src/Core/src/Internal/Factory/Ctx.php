<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Factory;

final class Ctx
{
    public function __construct(
        public string $alias,
        public string $class,
        public ?string $parameter = null,
        public ?bool $singleton = null,
        public ?\ReflectionClass $reflection = null,
    ) {
    }
}
