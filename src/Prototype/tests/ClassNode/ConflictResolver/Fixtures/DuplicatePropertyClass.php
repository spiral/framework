<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;

class DuplicatePropertyClass
{
    public function update($test): void
    {
    }

    public function view(): void
    {
        $this->test;
    }
}
