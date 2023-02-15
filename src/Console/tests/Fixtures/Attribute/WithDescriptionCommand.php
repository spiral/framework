<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures\Attribute;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(name: 'attribute-with-description', description: 'Some description text')]
final class WithDescriptionCommand extends Command
{
    public function perform(): int
    {
        $this->write($this->getDescription());

        return self::SUCCESS;
    }
}
