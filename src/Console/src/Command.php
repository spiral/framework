<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Attributes\Factory;
use Spiral\Console\Configurator\Attribute\Parser as AttributeParser;
use Spiral\Console\Configurator\AttributeConfigurator;
use Spiral\Console\Configurator\Configurator;
use Spiral\Console\Configurator\Signature\Parser as SignatureParser;
use Spiral\Console\Configurator\SignatureConfigurator;
use Spiral\Console\Event\CommandFinished;
use Spiral\Console\Event\CommandStarting;
use Spiral\Console\Interceptor\AttributeInterceptor;
use Spiral\Console\Traits\HelpersTrait;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\InterceptableCore;
use Spiral\Events\EventDispatcherAwareInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides automatic command configuration and access to global container scope.
 */
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

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
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

            $this->eventDispatcher?->dispatch(new CommandStarting($this, $this->input, $this->output));

            // Executing perform method with method injection
            $code = (int)$core->callAction(static::class, $method, [
                'input' => $this->input,
                'output' => $this->output,
                'command' => $this,
            ]);

            $this->eventDispatcher?->dispatch(new CommandFinished($this, $code, $this->input, $this->output));

            return $code;
        } finally {
            [$this->input, $this->output] = [null, null];
        }
    }

    protected function buildCore(): CoreInterface
    {
        $core = $this->container->get(CommandCore::class);

        $interceptableCore = new InterceptableCore($core, $this->eventDispatcher);

        foreach ($this->interceptors as $interceptor) {
            $interceptableCore->addInterceptor($this->container->get($interceptor));
        }
        $interceptableCore->addInterceptor($this->container->get(AttributeInterceptor::class));

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
        $configurator = new Configurator([
            new SignatureConfigurator(new SignatureParser()),
            new AttributeConfigurator(new AttributeParser((new Factory())->create()))
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
}
