<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

class CleanController
{
    public function test(string $id)
    {
        return $id;
    }

    public function missing(\SomeClass $arg): void
    {
    }

    protected function another(): void
    {
    }
}
