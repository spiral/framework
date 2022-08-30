<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Console\Traits\HelpersTrait;
use Spiral\Core\Container;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\ResolverInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides automatic command configuration and access to global container scope.
 */
abstract class Command extends SymfonyCommand
{
    use HelpersTrait;

    // Command name.
    protected const NAME = '';

    //  Short command description.
    protected const DESCRIPTION = '';

    // Command options specified in Symphony format. For more complex definitions redefine
    // getOptions() method.
    protected const OPTIONS = [];

    // Command arguments specified in Symphony format. For more complex definitions redefine
    // getArguments() method.
    protected const ARGUMENTS = [];

    /** @var Container|null */
    protected $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * Pass execution to "perform" method using container to resolve method dependencies.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->container === null) {
            throw new ScopeException('Container is not set');
        }

        $reflection = new \ReflectionMethod($this, 'perform');
        $reflection->setAccessible(true);

        $resolver = $this->container->get(ResolverInterface::class);

        try {
            [$this->input, $this->output] = [$input, $output];

            //Executing perform method with method injection
            return (int)$reflection->invokeArgs($this, $resolver->resolveArguments(
                $reflection,
                compact('input', 'output')
            ));
        } finally {
            [$this->input, $this->output] = [null, null];
        }
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);

        foreach ($this->defineOptions() as $option) {
            call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            call_user_func_array([$this, 'addArgument'], $argument);
        }
    }

    /**
     * Define command options.
     */
    protected function defineOptions(): array
    {
        return static::OPTIONS;
    }

    /**
     * Define command arguments.
     */
    protected function defineArguments(): array
    {
        return static::ARGUMENTS;
    }
}
