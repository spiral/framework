<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class CommandDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        /** @var InputArgument[] */
        public readonly array $arguments = [],
        /** @var InputOption[] */
        public readonly array $options = [],
        /** @var ?non-empty-string */
        public readonly ?string $description = null,
        /** @var ?non-empty-string */
        public readonly ?string $help = null
    ) {
    }
}
