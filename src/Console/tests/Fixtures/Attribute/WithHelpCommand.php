<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures\Attribute;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(name: 'attribute-with-help', help: 'Some help message')]
final class WithHelpCommand extends Command
{
    public function perform(): int
    {
        $this->write($this->getHelp());

        return self::SUCCESS;
    }
}
