<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

abstract class LightEngine implements EngineInterface
{
    protected int $power;

    public function getPower(): int
    {
        return $this->power;
    }

    public function withPower(int $value): static
    {
        $clone = clone $this;
        $clone->power = $value;

        return $clone;
    }

    public static function isWroomWroom(): bool
    {
        return true;
    }
}
