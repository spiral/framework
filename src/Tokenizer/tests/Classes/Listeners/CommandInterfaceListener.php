<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Listeners;

use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandInterface;
use Spiral\Tokenizer\Attribute\ListenForClasses;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[ListenForClasses(target: ConsoleCommandInterface::class)]
class CommandInterfaceListener implements TokenizationListenerInterface
{
    public function listen(\ReflectionClass $class): void
    {
        // TODO: Implement listen() method.
    }

    public function finalize(): void
    {
        // TODO: Implement finalize() method.
    }
}

