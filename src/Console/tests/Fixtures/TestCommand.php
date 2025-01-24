<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures;

use Spiral\Console\Command;
use Spiral\Core\Attribute\Singleton;

#[Singleton]
class TestCommand extends Command
{
    public const NAME = 'test';
    public const DESCRIPTION = 'Test Command';

    private int $count = 0;

    public function perform(): void
    {
        $this->write('Hello World - ' . ($this->count++));
    }
}
