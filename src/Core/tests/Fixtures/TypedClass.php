<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

class TypedClass
{
    public function __construct(
        public string $string,
        public int $int,
        public float $float,
        public bool $bool,
        public array $array = [],
        public ?string $pong = null
    ) {
    }
}
