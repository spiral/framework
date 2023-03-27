<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\JobHandlerDeclaration;

#[AsCommand(name: 'create:jobHandler', description: 'Create job handler declaration')]
class JobHandlerCommand extends AbstractCommand
{
    #[Argument(description: 'Job handler name')]
    #[Question(question: 'What would you like to name the Job handler?')]
    private string $name;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    public function perform(): int
    {
        $declaration = $this->createDeclaration(JobHandlerDeclaration::class);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
