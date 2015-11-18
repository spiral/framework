<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Console;

use Spiral\Console\Configs\ConsoleConfig;
use Spiral\Console\Exceptions\ConsoleException;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Core;
use Spiral\Core\DispatcherInterface;
use Spiral\Core\HippocampusInterface;
use Spiral\Core\Loader;
use Spiral\Debug\BenchmarkerInterface;
use Spiral\Debug\Debugger;
use Spiral\Debug\SnapshotInterface;
use Spiral\Tokenizer\LocatorInterface;
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
 */
class ConsoleDispatcher extends Component implements SingletonInterface, DispatcherInterface
{
    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Undefined response code for command (errors).
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
     * @var ConsoleConfig
     */
    protected $config = null;

    /**
     * To prevent mixing of web and console loadmaps we would like to get and configure our own
     * Loader.
     *
     * @var Loader
     */
    protected $loader = null;

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
     * @var LocatorInterface
     */
    protected $locator = null;

    /**
     * @param ConsoleConfig        $config
     * @param ContainerInterface   $container
     * @param HippocampusInterface $memory
     * @param LocatorInterface     $locator
     * @param Loader               $loader
     */
    public function __construct(
        ConsoleConfig $config,
        ContainerInterface $container,
        HippocampusInterface $memory,
        LocatorInterface $locator,
        Loader $loader
    ) {
        $this->config = $config;

        $this->container = $container;
        $this->memory = $memory;
        $this->locator = $locator;
        $this->loader = $loader;

        //Trying to load list of commands from memory cache
        $this->commands = $memory->loadData('commands');
        if (!is_array($this->commands)) {
            $this->commands = [];
        }
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
            $this->findCommands();
        }

        foreach ($this->commands as $command) {
            try {
                //Constructing command class
                $command = $this->container->construct($command);
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
    public function start()
    {
        //Some console commands utilizes benchmarking, let's help them
        $this->container->bind(BenchmarkerInterface::class, Debugger::class);

        //Let's disable loader in console mode
        $this->loader->disable();

        $this->application()->run();
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

        $this->openScope($input, $output);
        $code = self::CODE_UNDEFINED;

        try {

            //Go
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
    public function findCommands()
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