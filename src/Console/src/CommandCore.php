<?php

declare(strict_types=1);

namespace Spiral\Console;

use Spiral\Core\CoreInterface;
use Spiral\Core\InvokerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandCore implements CoreInterface
{
    public function __construct(
        private readonly InvokerInterface $invoker
    ) {
    }

    /**
     * @param array{
     *     input: InputInterface,
     *     output: OutputInterface,
     *     command: Command
     * } $parameters
     */
    public function callAction(string $commandName, string $method, array $parameters = []): int
    {
        $input = $parameters['input'];
        $output = $parameters['output'];
        $command = $parameters['command'];

        return (int)$this->invoker->invoke([$command, $method], \compact('input', 'output'));
    }
}
