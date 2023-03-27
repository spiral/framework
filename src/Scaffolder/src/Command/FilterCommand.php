<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\FilterDeclaration;

#[AsCommand(name: 'create:filter', description: 'Create Request filter declaration')]
final class FilterCommand extends AbstractCommand
{
    #[Argument(description: 'Request filter name')]
    #[Question(question: 'What would you like to name the Request filter?')]
    private string $name;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    #[Option(name: 'validate', shortcut: 's', description: 'Use validator to validate request data')]
    private bool $useValidator = false;

    #[Option(name: 'property', shortcut: 'p', description: 'Filter properties')]
    private array $properties = [];

    public function perform(): int
    {
        $declaration = $this->createDeclaration(FilterDeclaration::class);

        foreach ($this->properties as $property) {
            $declaration->addProperty($property);
        }

        if ($this->useValidator) {
            $declaration->addFilterDefinition();
        }

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
