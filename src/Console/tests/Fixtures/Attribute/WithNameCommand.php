<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures\Attribute;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(name: 'attribute-with-name')]
final class WithNameCommand extends Command
{
    public function perform(): int
    {
        $this->write($this->getName());

        return self::SUCCESS;
    }
}
