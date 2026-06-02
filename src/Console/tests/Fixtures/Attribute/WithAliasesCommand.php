<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures\Attribute;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(name: 'attribute-with-aliases', aliases: ['awa', 'alias-for-with-aliases'])]
final class WithAliasesCommand extends Command
{
    public function perform(): int
    {
        $this->write(\implode(',', $this->getAliases()));

        return self::SUCCESS;
    }
}
