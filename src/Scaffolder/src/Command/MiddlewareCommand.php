<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\MiddlewareDeclaration;

#[AsCommand(name: 'create:middleware', description: 'Create middleware declaration')]
class MiddlewareCommand extends AbstractCommand
{
    #[Argument(description: 'Middleware name')]
    #[Question(question: 'What would you like to name the Middleware?')]
    private string $name;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    public function perform(): int
    {
        $declaration = $this->createDeclaration(MiddlewareDeclaration::class);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
