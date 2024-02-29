<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Attribute\DispatcherScope;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Console\Logger\DebugListener;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Framework\Spiral;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
#[DispatcherScope(scope: Spiral::Console)]
final class ConsoleDispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly FinalizerInterface $finalizer,
        private readonly ContainerInterface $container,
        private readonly ExceptionHandlerInterface $errorHandler,
    ) {
    }

    public static function canServe(EnvironmentInterface $env): bool
    {
        // only run in pure CLI more, ignore under RoadRunner
        return (PHP_SAPI === 'cli' && $env->get('RR_MODE') === null);
    }

    public function serve(InputInterface $input = null, OutputInterface $output = null): int
    {
        // On demand to save some memory.

        $output ??= new ConsoleOutput();

        /** @var DebugListener $listener */
        $listener = $this->container->get(DebugListener::class);
        $listener = $listener->withOutput($output)->enable();

        /** @var Console $console */
        $console = $this->container->get(Console::class);

        try {
            return $console->start($input ?? new ArgvInput(), $output);
        } catch (Throwable $e) {
            $this->handleException($e, $output);

            return 255;
        } finally {
            $listener->disable();
            $this->finalizer->finalize(false);
        }
    }

    protected function handleException(Throwable $exception, OutputInterface $output): void
    {
        $this->errorHandler->report($exception);
        $output->write(
            $this->errorHandler->render(
                $exception,
                verbosity: $this->mapVerbosity($output),
                format: 'cli',
            )
        );
    }

    private function mapVerbosity(OutputInterface $output): Verbosity
    {
        return match (true) {
            $output->isDebug() => Verbosity::DEBUG,
            $output->isVeryVerbose() => Verbosity::VERBOSE,
            default => Verbosity::BASIC
        };
    }
}
