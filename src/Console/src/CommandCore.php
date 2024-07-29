<?php

declare(strict_types=1);

namespace Spiral\Console;

use Spiral\Core\Attribute\Scope as ScopeAttribute;
use Spiral\Core\CoreInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ScopeAttribute('console.command')]
final class CommandCore implements CoreInterface, HandlerInterface
{
    public function __construct(
        private readonly InvokerInterface $invoker,
    ) {
    }

    /**
     * @param array{input: InputInterface, output: OutputInterface, command: Command}|array $parameters
     */
    public function callAction(string $controller, string $action, array $parameters = []): int
    {
        $command = $parameters['command'];

        return (int)$this->invoker->invoke([$command, $action]);
    }

    public function handle(CallContextInterface $context): int
    {
        $callable = $context->getTarget()->getCallable() ?? throw new \RuntimeException('Command action not found');
        return (int)$this->invoker->invoke($callable, $context->getArguments());
    }
}
