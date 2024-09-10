<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Spiral\Console\Command;

/**
 * @internal
 */
final class ConstantBasedConfigurator implements ConfiguratorInterface
{
    public function configure(Command $command, \ReflectionClass $reflection): void
    {
        $command->setName($reflection->getConstant('NAME'));
        $command->setDescription((string) $reflection->getConstant('DESCRIPTION'));

        foreach ($reflection->getMethod('defineOptions')->invoke($command) as $option) {
            $command->addOption(...$option);
        }

        foreach ($reflection->getMethod('defineArguments')->invoke($command) as $argument) {
            $command->addArgument(...$argument);
        }
    }

    public function canConfigure(Command $command, \ReflectionClass $reflection): bool
    {
        return true;
    }
}
