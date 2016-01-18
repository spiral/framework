<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Console;

use Monolog\Handler\HandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use Spiral\Console\Exceptions\ConsoleException;
use Spiral\Console\Logging\ConsoleHandler;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Core;
use Spiral\Core\DispatcherInterface;
use Spiral\Core\HippocampusInterface;
use Spiral\Core\Loader;
use Spiral\Debug\BenchmarkerInterface;
use Spiral\Debug\Debugger;
use Spiral\Debug\LogsInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Tokenizer\ClassLocatorInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Used as application dispatcher in console mode. Can execute automatically locate and execute
 * every available Symfony command.
 * 
 * @todo optimize
 */
class ConsoleDispatcher extends Component implements SingletonInterface, DispatcherInterface
{
    const SINGLETON = self::class;

    /**
     * Undefined response code for command (errors). See below.
     */
    const CODE_UNDEFINED = 102;

    /**
     * @var Application
     */
    private $application = null;

    /**
     * @var array
     */
    private $commands = [];

    /**
     * Needed to set valid scope for commands.
     *
     * @var mixed
     */
    private $inputScope = null;

    /**
     * Needed to set valid scope for commands.
     *
     * @var mixed
     */
    private $outputScope = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @invisible
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @var ClassLocatorInterface
     */
    protected $locator = null;

    /**
     * @var Debugger
     */
    protected $debugger = null;

    /**
     * @param ContainerInterface    $container
     * @param HippocampusInterface  $memory
     * @param ClassLocatorInterface $locator
     * @param Debugger              $debugger
     */
    public function __construct(
        ContainerInterface $container,
        HippocampusInterface $memory,
        ClassLocatorInterface $locator,
        Debugger $debugger
    ) {
        $this->container = $container;
        $this->memory = $memory;
        $this->locator = $locator;

        //Trying to load list of commands from memory cache
        $this->commands = $memory->loadData('commands');
        if (!is_array($this->commands)) {
            $this->commands = [];
        }

        $this->debugger = $debugger;
    }

    /**
     * Get or create instance of ConsoleApplication.
     *
     * @return Application
     */
    public function application()
    {
        if (!empty($this->application)) {
            return $this->application;
        }

        $this->application = new Application('Spiral Console Toolkit', Core::VERSION);

        //Commands lookup
        if (empty($this->commands)) {
            $this->locateCommands();
        }

        foreach ($this->commands as $command) {
            try {
                //Constructing command class
                $command = $this->container->make($command);
                if (method_exists($command, 'isAvailable') && !$command->isAvailable()) {
                    continue;
                }
            } catch (\Exception $exception) {
                continue;
            }

            $this->application->add($command);
        }

        return $this->application;
    }

    /**
     * {@inheritdoc}
     */
    public function start(ConsoleHandler $handler = null)
    {
        //Some console commands utilizes benchmarking, let's help them
        $this->container->bind(BenchmarkerInterface::class, Debugger::class);

        $output = new ConsoleOutput();
        $this->debugger->shareHandler($this->consoleHandler($output));

        $scope = $this->container->replace(LogsInterface::class, $this->debugger);

        try {
            $this->application()->run(null, $output);
        } finally {
            $this->container->restore($scope);
        }
    }

    /**
     * Execute console command using it's name.
     *
     * @param string               $command
     * @param array|InputInterface $input
     * @param OutputInterface      $output
     * @return CommandOutput
     * @throws ConsoleException
     */
    public function command($command, $input = [], OutputInterface $output = null)
    {
        if (is_array($input)) {
            $input = new ArrayInput(compact('command') + $input);
        }

        if (empty($output)) {
            $output = new BufferedOutput();
        }

        //todo: do we need input scope?
        $this->openScope($input, $output);
        $code = self::CODE_UNDEFINED;

        try {
            /**
             * Debug: this method creates scope for [[InputInterface]] and [[OutputInterface]].
             */
            $code = $this->application()->find($command)->run($input, $output);
        } catch (\Exception $exception) {
            $this->application->renderException($exception, $output);
        } finally {
            $this->restoreScope();
        }

        return new CommandOutput($code, $output);
    }

    /**
     * Locate every available Symfony command using Tokenizer.
     *
     * @return array
     */
    public function locateCommands()
    {
        $this->commands = [];
        foreach ($this->locator->getClasses(SymfonyCommand::class) as $class) {
            if ($class['abstract']) {
                continue;
            }

            $this->commands[] = $class['name'];
        }

        $this->memory->saveData('commands', $this->commands);

        return $this->commands;
    }

    /**
     * List of all available command names.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * {@inheritdoc}
     *
     * @param OutputInterface $output
     */
    public function handleSnapshot(SnapshotInterface $snapshot, OutputInterface $output = null)
    {
        if (empty($output)) {
            $output = new ConsoleOutput(OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->application()->renderException($snapshot->getException(), $output);
    }

    /**
     * Console handler for Monolog logger.
     *
     * @param OutputInterface $output
     * @return HandlerInterface
     */
    protected function consoleHandler(OutputInterface $output)
    {
        return new ConsoleHandler($output);
    }

    /**
     * Creating input/output scope in container.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function openScope(InputInterface $input, OutputInterface $output)
    {
        $this->inputScope = $this->container->replace(InputInterface::class, $input);
        $this->outputScope = $this->container->replace(OutputInterface::class, $output);
    }

    /**
     * Restoring input and output scopes.
     */
    private function restoreScope()
    {
        $this->container->restore($this->inputScope);
        $this->container->restore($this->outputScope);
    }
}
