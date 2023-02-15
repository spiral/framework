<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\JobHandlerDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Question(
    question: 'What would you like to name the Job handler?',
    argument: 'name'
)]
class JobHandlerCommand extends AbstractCommand
{
    protected const NAME        = 'create:jobHandler';
    protected const DESCRIPTION = 'Create job handler declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'job handler name'],
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
     * Create jobHandler declaration.
     */
    public function perform(): int
    {
        $declaration = $this->createDeclaration(JobHandlerDeclaration::class);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
