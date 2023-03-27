<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\ConfigDeclaration;

#[AsCommand(name: 'create:config', description: 'Create config declaration')]
class ConfigCommand extends AbstractCommand
{
    #[Argument(description: 'Сonfig name, or a config filename if "-r" flag is set ({path/to/configs/directory/}{config/filename}.php)')]
    #[Question(question: 'Please provide the name of the Config class, or the filename of an existing config file')]
    private string $name;

    #[Option(shortcut: 'r', description: 'Create config class based on a given config filename')]
    private bool $reverse = false;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    public function perform(): int
    {
        $declaration = $this->createDeclaration(ConfigDeclaration::class);
        $declaration->create($this->reverse, $this->name);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
