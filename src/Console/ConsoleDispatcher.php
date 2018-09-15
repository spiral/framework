<?php
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
use Spiral\Exceptions\ConsoleHandler;
use Spiral\Snapshots\SnapshotterInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
class ConsoleDispatcher implements DispatcherInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param EnvironmentInterface $environment
     * @param ContainerInterface   $container
     */
    public function __construct(EnvironmentInterface $environment, ContainerInterface $container)
    {
        $this->environment = $environment;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function canServe(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner
        return (php_sapi_name() == 'cli' && $this->environment->get('RR') === null);
    }

    /**
     * @inheritdoc
     */
    public function serve()
    {
        /** @var ConsoleCore $core */
        $core = $this->container->get(ConsoleCore::class);
        $output = new ConsoleOutput();

        try {
            $core->start(new ArgvInput(), $output);
        } catch (\Throwable $e) {
            $this->renderException($e, $output);
        }
    }

    /**
     * @param \Throwable      $e
     * @param OutputInterface $output
     */
    protected function renderException(\Throwable $e, OutputInterface $output)
    {
        try {
            $this->container->get(SnapshotterInterface::class)->register($e);
        } catch (\Throwable|ContainerExceptionInterface $sne) {
            // no need to notify when unable to register an exception
        }

        // Explaining exception to the user
        $handler = new ConsoleHandler(STDERR);
        $output->write($handler->renderException($e, $this->getVerbosity($output)));
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    private function getVerbosity(OutputInterface $output): int
    {
        if ($output->isDebug() || $output->isVeryVerbose()) {
            return ConsoleHandler::VERBOSITY_DEBUG;
        }

        if ($output->isVerbose()) {
            return ConsoleHandler::VERBOSITY_VERBOSE;
        }

        return ConsoleHandler::VERBOSITY_BASIC;
    }
}