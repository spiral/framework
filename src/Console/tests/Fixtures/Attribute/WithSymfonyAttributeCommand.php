<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures\Attribute;

use Spiral\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'attribute-with-sf-command-attr', description: 'Some description text')]
final class WithSymfonyAttributeCommand extends Command
{
    public function perform(): int
    {
        $this->write($this->getDescription());
        $this->write('|');
        $this->write($this->getName());

        return self::SUCCESS;
    }
}
