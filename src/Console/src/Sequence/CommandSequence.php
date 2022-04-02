<?php

declare(strict_types=1);

namespace Spiral\Console\Sequence;

use Psr\Container\ContainerInterface;
use Spiral\Console\Console;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes command as part of the sequence.
 */
final class CommandSequence extends AbstractSequence
{
    public function __construct(
        private readonly string $command,
        private readonly array $options = [],
        string $header = '',
        string $footer = ''
    ) {
        parent::__construct($header, $footer);
    }

    public function execute(ContainerInterface $container, OutputInterface $output): void
    {
        /** @var Console $console */
        $console = $container->get(Console::class);

        $console->run($this->command, $this->options, $output);
    }
}
