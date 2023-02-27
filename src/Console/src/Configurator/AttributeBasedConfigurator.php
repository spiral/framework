<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Spiral\Console\Command;
use Spiral\Console\Configurator\Attribute\Parser;

/**
 * @internal
 */
final class AttributeBasedConfigurator implements ConfiguratorInterface
{
    public function __construct(
        private readonly Parser $parser
    ) {
    }

    public function canConfigure(Command $command, \ReflectionClass $reflection): bool
    {
        return $this->parser->hasCommandAttribute($reflection);
    }

    public function configure(Command $command, \ReflectionClass $reflection): void
    {
        $result = $this->parser->parse($reflection);

        $command->setName($result->name);
        $command->setDescription($result->description ?? (string) $reflection->getConstant('DESCRIPTION'));
        $command->setHelp((string) $result->help);

        foreach ($result->options as $option) {
            $command->getDefinition()->addOption($option);
        }

        foreach ($result->arguments as $argument) {
            $command->getDefinition()->addArgument($argument);
        }
    }
}
