<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\ControllerDeclaration;

#[AsCommand(name: 'create:controller', description: 'Create controller declaration')]
class ControllerCommand extends AbstractCommand
{
    #[Argument(description: 'Controller name')]
    #[Question(question: 'What would you like to name the Controller?')]
    private string $name;

    #[Option(name: 'action', shortcut: 'a', description: 'Pre-create controller action')]
    private array $actions = [];

    #[Option(name: 'prototype', shortcut: 'p', description: 'Add \Spiral\Prototype\Traits\PrototypeTrait to controller')]
    private bool $usePrototype = false;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    public function perform(): int
    {
        $declaration = $this->createDeclaration(ControllerDeclaration::class);

        foreach ($this->actions as $action) {
            $declaration->addAction($action);
        }

        if ($this->usePrototype) {
            $declaration->addPrototypeTrait();
        }

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
