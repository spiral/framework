<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

class EngineVAZ2101 extends LightEngine implements MadeInUssrInterface
{
    public const NAME = 'VAZ 2101';

    protected int $power = 59;

    public function getName(): string
    {
        return static::NAME;
    }

    public function rust(float $index): self
    {
        $this->power = (int)ceil($this->power / $index);
        return $this;
    }
}
