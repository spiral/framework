<?php

declare(strict_types=1);

namespace Spiral\Console;

use Spiral\Console\Exception\LocatorException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

interface LocatorInterface
{
    /**
     * Get all available command class names.
     *
     * @return SymfonyCommand[]
     *
     * @throws LocatorException
     */
    public function locateCommands(): array;
}
