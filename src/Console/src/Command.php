<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Event\CommandFinished;
use Spiral\Console\Event\CommandStarting;
use Spiral\Console\Signature\Parser;
use Spiral\Console\Traits\HelpersTrait;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\InterceptableCore;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides automatic command configuration and access to global container scope.
 */
abstract class Command extends SymfonyCommand
{
    use HelpersTrait;

    /** Command name. */
    protected const NAME = '';
    /** Short command description. */
    protected const DESCRIPTION = null;
    /** Command signature. */
    protected const SIGNATURE = null;
    /** Command options specified in Symfony format. For more complex definitions redefine getOptions() method. */
    protected const OPTIONS = [];
    /** Command arguments specified in Symfony format. For more complex definitions redefine getArguments() method. */
    protected const ARGUMENTS = [];

    protected ?ContainerInterface $container = null;

    /** @var array<class-string<CoreInterceptorInterface>> */
    protected array $interceptors = [];

    /** {@internal} */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * {@internal}
     * @param array<class-string<CoreInterceptorInterface>> $interceptors
     */
    public function setInterceptors(array $interceptors): void
    {
        $this->interceptors = $interceptors;
    }

    /**
     * Pass execution to "perform" method using container to resolve method dependencies.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->container === null) {
            throw new ScopeException('Container is not set');
        }

        $method = method_exists($this, 'perform') ? 'perform' : '__invoke';

        $core = $this->buildCore();

        try {
            [$this->input, $this->output] = [$this->prepareInput($input), $this->prepareOutput($input, $output)];

            $dispatcher = $this->container->has(EventDispatcherInterface::class)
                ? $this->container->get(EventDispatcherInterface::class)
                : null;

            $dispatcher?->dispatch(new CommandStarting($this, $this->input, $this->output));

            // Executing perform method with method injection
            $code = (int)$core->callAction(static::class, $method, [
                'input' => $this->input,
                'output' => $this->output,
                'command' => $this,
            ]);

            $dispatcher?->dispatch(new CommandFinished($this, $code, $this->input, $this->output));

            return $code;
        } finally {
            [$this->input, $this->output] = [null, null];
        }
    }

    protected function buildCore(): CoreInterface
    {
        $core = $this->container->get(CommandCore::class);
        $dispatcher = $this->container->has(EventDispatcherInterface::class)
            ? $this->container->get(EventDispatcherInterface::class)
            : null;

        $interceptableCore = new InterceptableCore($core, $dispatcher);

        foreach ($this->interceptors as $interceptor) {
            $interceptableCore->addInterceptor($this->container->get($interceptor));
        }

        return $interceptableCore;
    }

    protected function prepareInput(InputInterface $input): InputInterface
    {
        return $input;
    }

    protected function prepareOutput(InputInterface $input, OutputInterface $output): OutputInterface
    {
        return new SymfonyStyle($input, $output);
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        if (static::SIGNATURE !== null) {
            $this->configureViaSignature((string)static::SIGNATURE);
        } else {
            $this->setName(static::NAME);
        }

        $this->setDescription((string)static::DESCRIPTION);

        foreach ($this->defineOptions() as $option) {
            \call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            \call_user_func_array([$this, 'addArgument'], $argument);
        }
    }

    protected function configureViaSignature(string $signature): void
    {
        $result = (new Parser())->parse($signature);

        $this->setName($result->name);

        foreach ($result->options as $option) {
            $this->getDefinition()->addOption($option);
        }

        foreach ($result->arguments as $argument) {
            $this->getDefinition()->addArgument($argument);
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
