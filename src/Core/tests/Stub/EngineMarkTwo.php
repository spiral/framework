<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

final class EngineMarkTwo extends LightEngine
{
    public const NAME = 'Mark Two';

    protected int $power = 160;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getPower(): int
    {
        return $this->power;
    }
}
