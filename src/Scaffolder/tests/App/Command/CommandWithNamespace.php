<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\App\Command;

use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class CommandWithNamespace extends AbstractCommand
{
    protected const NAME = 'create:command-with-namespace';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Command name'],
        ['alias', InputArgument::OPTIONAL, 'Command id/alias'],
    ];
    protected const OPTIONS     = [
        [
            'namespace',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional, specify a custom namespace',
        ],
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    public function __invoke(): int
    {
        $this->createDeclaration(CommandDeclaration::class);

        return self::SUCCESS;
    }
}
