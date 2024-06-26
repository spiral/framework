<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Attributes\Factory;
use Spiral\Console\Configurator\Attribute\Parser as AttributeParser;
use Spiral\Console\Configurator\AttributeBasedConfigurator;
use Spiral\Console\Configurator\Configurator;
use Spiral\Console\Configurator\Signature\Parser as SignatureParser;
use Spiral\Console\Configurator\SignatureBasedConfigurator;
use Spiral\Console\Event\CommandFinished;
use Spiral\Console\Event\CommandStarting;
use Spiral\Console\Traits\HelpersTrait;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\InvokerInterface;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\Events\EventDispatcherAwareInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides automatic command configuration and access to global container scope.
 */
#[\Spiral\Core\Attribute\Scope('console')]
abstract class Command extends SymfonyCommand implements EventDispatcherAwareInterface
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
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /** @var array<class-string<CoreInterceptorInterface|InterceptorInterface>> */
    protected array $interceptors = [];

    /** @internal */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @internal
     * @param array<class-string<CoreInterceptorInterface|InterceptorInterface>> $interceptors
     */
    public function setInterceptors(array $interceptors): void
    {
        $this->interceptors = $interceptors;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Pass execution to "perform" method using container to resolve method dependencies.
     * @final
     * @TODO Change to final in v4.0
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->container === null) {
            throw new ScopeException('Container is not set');
        }

        $method = method_exists($this, 'perform') ? 'perform' : '__invoke';

        try {
            [$this->input, $this->output] = [$this->prepareInput($input), $this->prepareOutput($input, $output)];

            $this->eventDispatcher?->dispatch(new CommandStarting($this, $this->input, $this->output));


            // Executing perform method with method injection
            $code = $this->container->get(ScopeInterface::class)
                ->runScope(
                    new Scope(
                        name: 'console.command',
                        bindings: [
                            InputInterface::class => $input,
                            OutputInterface::class => $output,
                        ],
                        autowire: false,
                    ),
                    function () use ($method) {
                        $core = $this->buildCore();
                        $arguments = ['input' => $this->input, 'output' => $this->output, 'command' => $this];

                        return $core instanceof HandlerInterface
                            ? (int)$core->handle(new CallContext(
                                Target::fromPair($this, $method),
                                $arguments,
                            ))
                            : (int)$core->callAction(static::class, $method, $arguments);
                    },
                );

            $this->eventDispatcher?->dispatch(new CommandFinished($this, $code, $this->input, $this->output));

            return $code;
        } finally {
            [$this->input, $this->output] = [null, null];
        }
    }

    /**
     * @deprecated This method will be removed in v4.0.
     */
    protected function buildCore(): CoreInterface|HandlerInterface
    {
        /** @var InvokerInterface $invoker */
        $invoker = $this->container->get(InvokerInterface::class);
        return $invoker->invoke([CommandCoreFactory::class, 'make'], [$this->interceptors, $this->eventDispatcher]);
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
        $configurator = new Configurator([
            new SignatureBasedConfigurator(new SignatureParser()),
            new AttributeBasedConfigurator(new AttributeParser((new Factory())->create())),
        ]);
        $configurator->configure($this, new \ReflectionClass($this));
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

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);

        $this->container?->get(PromptArguments::class)->promptMissedArguments($this, $input, $output);
    }
}
