<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 * @see     \Spiral\Console\Config\ConsoleConfig
 */

declare(strict_types=1);

use Spiral\Scaffolder\Command;

return [
    'commands' => [
        Command\BootloaderCommand::class,
        Command\CommandCommand::class,
        Command\ConfigCommand::class,
        Command\JobHandlerCommand::class,
        Command\ControllerCommand::class,
        Command\FilterCommand::class,
        Command\MiddlewareCommand::class,
    ]
];
