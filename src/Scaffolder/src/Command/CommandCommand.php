<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandCommand extends AbstractCommand
{
    protected const NAME        = 'create:command';
    protected const DESCRIPTION = 'Create command declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Command name'],
        ['alias', InputArgument::OPTIONAL, 'Command id/alias'],
    ];
    protected const OPTIONS     = [
        [
            'description',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Command description',
        ],
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create command declaration.
     */
    public function perform(): int
    {
        $declaration = $this->createDeclaration(CommandDeclaration::class);

        $declaration->setAlias((string) ($this->argument('alias') ?? $this->argument('name')));
        $declaration->setDescription((string) $this->option('description'));

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
