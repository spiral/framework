<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\CommandDeclaration;

#[AsCommand(name: 'create:command', description: 'Create console command declaration')]
class CommandCommand extends AbstractCommand
{
    #[Argument(description: 'Command name')]
    #[Question(question: 'What would you like to name the console Command?')]
    private string $name;

    #[Argument(description: 'Command id/alias')]
    private ?string $alias = null;

    #[Option(shortcut: 'd', description: 'Command description')]
    private ?string $description = null;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    #[Option(name: 'argument', shortcut: 'a', description: 'Command arguments')]
    private array $arguments = [];

    #[Option(name: 'option', shortcut: 'o', description: 'Command options')]
    private array $options = [];

    public function perform(): int
    {
        $declaration = $this->createDeclaration(CommandDeclaration::class, [
            'description' => $this->description,
            'alias' => $this->alias ?? \strtolower((string) \preg_replace('/(?<!^)[A-Z]/', ':$0', $this->name)),
        ]);

        foreach ($this->arguments as $argument) {
            $declaration->addArgument($argument);
        }

        foreach ($this->options as $option) {
            $declaration->addOption($option);
        }

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
