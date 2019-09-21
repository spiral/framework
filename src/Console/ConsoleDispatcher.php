<?php

declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Console\Logger\DebugListener;
use Spiral\Exceptions\ConsoleHandler;
use Spiral\Snapshots\SnapshotterInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
final class ConsoleDispatcher implements DispatcherInterface
{
    /** @var EnvironmentInterface */
    private $env;

    /** @var ContainerInterface */
    private $container;

    /** @var FinalizerInterface */
    private $finalizer;

    /**
     * @param EnvironmentInterface $env
     * @param FinalizerInterface   $finalizer
     * @param ContainerInterface   $container
     */
    public function __construct(
        EnvironmentInterface $env,
        FinalizerInterface $finalizer,
        ContainerInterface $container
    ) {
        $this->env       = $env;
        $this->finalizer = $finalizer;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function canServe(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner
        return php_sapi_name() == 'cli' && $this->env->get('RR') === null;
    }

    /**
     * @inheritdoc
     */
    public function serve(InputInterface $input = null, OutputInterface $output = null): void
    {
        $output = $output ?? new ConsoleOutput();

        /** @var DebugListener $listener */
        $listener = $this->container->get(DebugListener::class);
        $listener = $listener->withOutput($output)->enable();

        /** @var Console $console */
        $console = $this->container->get(Console::class);

        try {
            $console->start($input ?? new ArgvInput(), $output);
        } catch (\Throwable $e) {
            $this->handleException($e, $output);
        } finally {
            $listener->disable();
            $this->finalizer->finalize(false);
        }
    }

    /**
     * @param \Throwable      $e
     * @param OutputInterface $output
     */
    protected function handleException(\Throwable $e, OutputInterface $output): void
    {
        try {
            $this->container->get(SnapshotterInterface::class)->register($e);
        } catch (\Throwable|ContainerExceptionInterface $se) {
            // no need to notify when unable to register an exception
        }

        // Explaining exception to the user
        $handler = new ConsoleHandler(STDERR);
        $output->write($handler->renderException($e, $this->mapVerbosity($output)));
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    private function mapVerbosity(OutputInterface $output): int
    {
        if ($output->isDebug()) {
            return ConsoleHandler::VERBOSITY_DEBUG;
        }

        if ($output->isVeryVerbose()) {
            return ConsoleHandler::VERBOSITY_VERBOSE;
        }

        return ConsoleHandler::VERBOSITY_BASIC;
    }
}
