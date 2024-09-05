<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\App\Command;

use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Symfony\Component\Console\Input\InputArgument;

final class CommandWithoutNamespace extends AbstractCommand
{
    protected const NAME = 'create:command-without-namespace';

    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Command name'],
        ['alias', InputArgument::OPTIONAL, 'Command id/alias'],
    ];

    public function __invoke(): int
    {
        $this->createDeclaration(CommandDeclaration::class);

        return self::SUCCESS;
    }
}