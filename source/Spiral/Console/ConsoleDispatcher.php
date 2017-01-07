<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Console;

use Spiral\Console\Configs\ConsoleConfig;
use Spiral\Console\Exceptions\ConsoleException;
use Spiral\Console\Logging\DebugHandler;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Core;
use Spiral\Core\DispatcherInterface;
use Spiral\Core\MemoryInterface;
use Spiral\Core\NullMemory;
use Spiral\Debug\LogManager;
use Spiral\Debug\SnapshotInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
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
     * Undefined response code for command (errors). See below.
     */
    const CODE_UNDEFINED = 102;

    /**
     * @var ConsoleApplication
     */
    private $application = null;

    /**
     * Active console output.
     *
     * @var OutputInterface
     */
    private $output = null;

    /**
     * @var ConsoleConfig
     */
    protected $config;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @invisible
     * @var MemoryInterface
     */
    protected $memory;

    /**
     * @invisible
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param ConsoleConfig           $config
     * @param ContainerInterface|null $container
     * @param MemoryInterface|null    $memory
     * @param LocatorInterface|null   $locator
     */
    public function __construct(
        ConsoleConfig $config,
        ContainerInterface $container = null,
        MemoryInterface $memory = null,
        LocatorInterface $locator = null
    ) {
        $this->container = $container ?? new Container();
        $this->memory = $memory ?? new NullMemory();
        $this->locator = $locator ?? new NullLocator();
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function start(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->output = $output ?? new ConsoleOutput();

        /**
         * Sharing common log handler in order to display debug messages in verbosity mode.
         */
        $this->container->get(LogManager::class)->debugHandler(new DebugHandler($this->output));

        //Container scope
        $scope = self::staticContainer($this->container);
        try {
            /*
             * Commands are being executed in isolated container scope.
             */
            $this->consoleApplication()->run($input, $this->output);
        } finally {
            //Restoring scopes
            self::staticContainer($scope);
        }
    }

    /**
     * Execute console command by it's name.
     *
     * @param string               $command
     * @param array|InputInterface $input
     * @param OutputInterface      $output
     *
     * @return CommandOutput
     *
     * @throws ConsoleException
     */
    public function command(
        string $command,
        $input = [],
        OutputInterface $output = null
    ): CommandOutput {
        if (is_array($input)) {
            $input = new ArrayInput(compact('command') + $input);
        }

        $output = $output ?? new BufferedOutput();

        //Each command are executed in a specific environment
        $scope = self::staticContainer($this->container);
        try {
            /**
             * Debug: this method creates scope for [[InputInterface]] and [[OutputInterface]].
             */
            $code = $this->consoleApplication()->find($command)->run($input, $output);
        } catch (\Throwable $e) {
            $this->application->renderException($e, $output);
        } finally {
            self::staticContainer($scope);
        }

        return new CommandOutput($code ?? self::CODE_UNDEFINED, $output);
    }

    /**
     * Get or create instance of ConsoleApplication.
     *
     * @return ConsoleApplication
     */
    public function consoleApplication()
    {
        if (!empty($this->application)) {
            //Already initiated
            return $this->application;
        }

        $this->application = new ConsoleApplication('Spiral Console Toolkit', Core::VERSION);
        $this->application->setCatchExceptions(false);

        foreach ($this->getCommands() as $command) {
            //Constructing command class
            $command = $this->container->get($command);

            if (method_exists($command, 'isAvailable') && !$command->isAvailable()) {
                //Command declares itself as non available
                continue;
            }

            $this->application->add($command);
        }

        return $this->application;
    }

    /**
     * Locate every available Symfony command using Tokenizer.
     *
     * @param bool $reset Ignore cache.
     *
     * @return array
     */
    public function getCommands(bool $reset = false): array
    {
        $commands = (array)$this->memory->loadData('commands');
        if (!empty($commands) && !$reset) {
            //Reading from cache
            return $commands + $this->config->userCommands();
        }

        if ($this->config->locateCommands()) {
            //Automatically locate commands
            $commands = $this->locator->locateCommands();
        }

        //Warming up cache
        $this->memory->saveData('commands', $commands);

        return $commands + $this->config->userCommands();
    }

    /**
     * {@inheritdoc}
     *
     * @param OutputInterface $output
     */
    public function handleSnapshot(SnapshotInterface $snapshot, OutputInterface $output = null)
    {
        $output = $output ??  $this->output ?? new ConsoleOutput(OutputInterface::VERBOSITY_VERBOSE);

        //Rendering exception in console
        $this->consoleApplication()->renderException($snapshot->getException(), $output);
    }
}