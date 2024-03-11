<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Exception\LocatorException;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\Events\EventDispatcherAwareInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

#[\Spiral\Core\Attribute\Scope('console')]
final class Console
{
    // Undefined response code for command (errors). See below.
    public const CODE_NONE = 102;

    private ?Application $application = null;

    public function __construct(
        private readonly ConsoleConfig $config,
        private readonly ?LocatorInterface $locator = null,
        #[Proxy] private readonly ContainerInterface $container = new Container(),
        private readonly ScopeInterface $scope = new Container(),
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }

    /**
     * Run console application.
     *
     * @throws \Throwable
     */
    public function start(InputInterface $input = new ArgvInput(), OutputInterface $output = new ConsoleOutput()): int
    {
        return $this->run(
            $input->getFirstArgument() ?? 'list',
            $input,
            $output,
        )->getCode();
    }

    /**
     * Run selected command.
     *
     * @throws \Throwable
     * @throws CommandNotFoundException
     */
    public function run(
        ?string $command,
        array|InputInterface $input = [],
        OutputInterface $output = new BufferedOutput(),
    ): CommandOutput {
        $input = \is_array($input) ? new ArrayInput($input) : $input;

        $this->configureIO($input, $output);

        if ($command !== null) {
            $input = new InputProxy($input, ['firstArgument' => $command]);
        }

        /**
         * @psalm-suppress InvalidArgument
         */
        $code = $this->scope->runScope(
            new Scope(
                bindings: [
                    InputInterface::class => $input,
                    OutputInterface::class => $output,
                ],
            ),
            fn () => $this->getApplication()->doRun($input, $output),
        );

        return new CommandOutput($code ?? self::CODE_NONE, $output);
    }

    /**
     * Get associated Symfony Console Application.
     *
     * @throws LocatorException
     */
    public function getApplication(): Application
    {
        if ($this->application !== null) {
            return $this->application;
        }

        $this->application = new Application($this->config->getName(), $this->config->getVersion());
        $this->application->setCatchExceptions(false);
        $this->application->setAutoExit(false);
        if ($this->dispatcher instanceof SymfonyEventDispatcherInterface) {
            $this->application->setDispatcher($this->dispatcher);
        }

        if ($this->locator !== null) {
            $this->addCommands($this->locator->locateCommands());
        }

        // Register user defined commands
        $static = new StaticLocator(
            $this->config->getCommands(),
            $this->config->getInterceptors(),
            $this->container,
        );

        $this->addCommands($static->locateCommands());

        return $this->application;
    }

    private function addCommands(iterable $commands): void
    {
        $interceptors = $this->config->getInterceptors();

        foreach ($commands as $command) {
            if ($command instanceof Command) {
                $command->setContainer($this->container);
                $command->setInterceptors($interceptors);
            }

            if ($this->dispatcher !== null && $command instanceof EventDispatcherAwareInterface) {
                $command->setEventDispatcher($this->dispatcher);
            }

            $this->application->add($command);
        }
    }

    /**
     * Extracted in order to manage command lifecycle.
     *
     * @see Application::configureIO()
     */
    private function configureIO(InputInterface $input, OutputInterface $output): void
    {
        if ($input->hasParameterOption(['--ansi'], true)) {
            $output->setDecorated(true);
        } elseif ($input->hasParameterOption(['--no-ansi'], true)) {
            $output->setDecorated(false);
        }

        if ($input->hasParameterOption(['--no-interaction', '-n'], true)) {
            $input->setInteractive(false);
        } elseif (\function_exists('posix_isatty')) {
            $inputStream = null;

            if ($input instanceof StreamableInputInterface) {
                $inputStream = $input->getStream();
            }

            if ($inputStream !== null && !@posix_isatty($inputStream) && false === getenv('SHELL_INTERACTIVE')) {
                $input->setInteractive(false);
            }
        }

        match ($shellVerbosity = (int)getenv('SHELL_VERBOSITY')) {
            -1 => $output->setVerbosity(OutputInterface::VERBOSITY_QUIET),
            1 => $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE),
            2 => $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE),
            3 => $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG),
            default => $shellVerbosity = 0
        };

        if ($input->hasParameterOption(['--quiet', '-q'], true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $shellVerbosity = -1;
        } else {
            if (
                $input->hasParameterOption('-vvv', true)
                || $input->hasParameterOption('--verbose=3', true)
                || 3 === $input->getParameterOption('--verbose', false, true)
            ) {
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                $shellVerbosity = 3;
            } elseif (
                $input->hasParameterOption('-vv', true)
                || $input->hasParameterOption('--verbose=2', true)
                || 2 === $input->getParameterOption('--verbose', false, true)
            ) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                $shellVerbosity = 2;
            } elseif (
                $input->hasParameterOption('-v', true)
                || $input->hasParameterOption('--verbose=1', true)
                || $input->hasParameterOption('--verbose', true)
                || $input->getParameterOption('--verbose', false, true)
            ) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $shellVerbosity = 1;
            }
        }

        if (-1 === $shellVerbosity) {
            $input->setInteractive(false);
        }

        \putenv('SHELL_VERBOSITY=' . $shellVerbosity);
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }
}
