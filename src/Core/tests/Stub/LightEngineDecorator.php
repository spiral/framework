<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

class LightEngineDecorator
{
    public function __construct(
        public LightEngine $engine
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        return $this->engine->{$name}(...$arguments);
    }
}
