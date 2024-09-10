<?php

declare(strict_types=1);

use Spiral\Scaffolder\Command\BootloaderCommand;
use Spiral\Scaffolder\Command\CommandCommand;
use Spiral\Scaffolder\Command\ConfigCommand;
use Spiral\Scaffolder\Command\JobHandlerCommand;
use Spiral\Scaffolder\Command\ControllerCommand;
use Spiral\Scaffolder\Command\MiddlewareCommand;
use Spiral\Scaffolder\Command;

return [
    'commands' => [
        BootloaderCommand::class,
        CommandCommand::class,
        ConfigCommand::class,
        JobHandlerCommand::class,
        ControllerCommand::class,
        MiddlewareCommand::class,
    ]
];
