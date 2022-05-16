<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

class EngineZIL130 implements EngineInterface, MadeInUssrInterface
{
    public const NAME = 'ZIL 130';

    private int $power = 148;

    public function getName(): string
    {
        return static::NAME;
    }

    public function getPower(): int
    {
        return $this->power;
    }
}
