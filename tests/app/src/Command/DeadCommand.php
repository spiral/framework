<?php

declare(strict_types=1);

namespace Spiral\App\Command;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(name: 'dead')]
class DeadCommand extends Command
{
    public const NAME = 'dead';

    public function perform(): void
    {
        throw new \InvalidArgumentException('This command is dead');
    }
}
