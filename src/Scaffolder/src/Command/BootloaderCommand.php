<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\BootloaderDeclaration;

#[AsCommand(name: 'create:bootloader', description: 'Create bootloader declaration')]
class BootloaderCommand extends AbstractCommand
{
    #[Argument(description: 'Bootloader name')]
    #[Question(question: 'What would you like to name the Bootloader?')]
    private string $name;

    #[Option(shortcut: 'c', description: 'Optional comment to add as class header')]
    private ?string $comment = null;

    #[Option(name: 'domain', shortcut: 'd', description: 'Mark as domain bootloader')]
    private bool $isDomain = false;

    #[Option(description: 'Optional, specify a custom namespace')]
    private ?string $namespace = null;

    public function perform(): int
    {
        $declaration = $this->createDeclaration(BootloaderDeclaration::class, [
            'isDomain' => $this->isDomain,
        ]);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
