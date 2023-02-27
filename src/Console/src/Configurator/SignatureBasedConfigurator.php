<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Spiral\Console\Command;
use Spiral\Console\Configurator\Signature\Parser;

/**
 * @internal
 */
final class SignatureBasedConfigurator implements ConfiguratorInterface
{
    public function __construct(
        private readonly Parser $parser
    ) {
    }

    public function canConfigure(Command $command, \ReflectionClass $reflection): bool
    {
        return $reflection->getConstant('SIGNATURE') !== null;
    }

    public function configure(Command $command, \ReflectionClass $reflection): void
    {
        $result = $this->parser->parse((string) $reflection->getConstant('SIGNATURE'));

        $command->setName($result->name);
        $command->setDescription($result->description ?? (string) $reflection->getConstant('DESCRIPTION'));

        foreach ($result->options as $option) {
            $command->getDefinition()->addOption($option);
        }

        foreach ($result->arguments as $argument) {
            $command->getDefinition()->addArgument($argument);
        }
    }
}
