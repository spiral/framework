<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Question(
    question: 'What would you like to name the Bootloader?',
    argument: 'name'
)]
class BootloaderCommand extends AbstractCommand
{
    protected const NAME        = 'create:bootloader';
    protected const DESCRIPTION = 'Create bootloader declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'bootloader name'],
    ];
    protected const OPTIONS     = [
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
        [
            'namespace',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional, specify a custom namespace',
        ],
    ];

    /**
     * Create bootloader declaration.
     */
    public function perform(): int
    {
        $declaration = $this->createDeclaration(BootloaderDeclaration::class);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
