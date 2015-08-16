<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Console;

use Spiral\Console\Exceptions\ConsoleException;
use Spiral\Core\Components\Loader;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Core;
use Spiral\Core\DispatcherInterface;
use Spiral\Core\HippocampusInterface;
use Spiral\Core\Singleton;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Debug\BenchmarkerInterface;
use Spiral\Debug\Debugger;
use Spiral\Debug\SnapshotInterface;
use Spiral\Tokenizer\TokenizerInterface;
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
class ConsoleDispatcher extends Singleton implements DispatcherInterface
{
    /**
     * ConsoleDispatcher does not need config for itseft, but some commands (Update for example)
     * needs configuration source.
     */
    use ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Configuration section.
     */
    const CONFIG = 'console';

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
     * @var TokenizerInterface
     */
    protected $tokenizer = null;

    /**
     * To prevent mixing of web and console loadmaps.
     *
     * @var Loader
     */
    protected $loader = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     * @param HippocampusInterface  $memory
     * @param TokenizerInterface    $tokenizer
     * @param Loader                $loader
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        ContainerInterface $container,
        HippocampusInterface $memory,
        TokenizerInterface $tokenizer,
        Loader $loader
    ) {
        $this->config = $configurator->getConfig(static::CONFIG);

        $this->container = $container;
        $this->memory = $memory;
        $this->tokenizer = $tokenizer;
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
        empty($this->commands) && $this->findCommands();
        foreach ($this->commands as $command) {
            try {
                $command = $this->container->get($command);
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
     * @param array|InputInterface $parameters
     * @param OutputInterface      $output
     * @return CommandOutput
     * @throws ConsoleException
     */
    public function command($command, $parameters = [], OutputInterface $output = null)
    {
        $input = is_object($parameters) ? $parameters : new ArrayInput(compact('command') + $parameters);
        $output = !empty($output) ? $output : new BufferedOutput();

        $outerOutput = $this->container->replace(OutputInterface::class, $output);
        $outerInput = $this->container->replace(InputInterface::class, $input);

        //Go
        $code = self::CODE_UNDEFINED;
        try {
            $code = $this->application()->find($command)->run($input, $output);
        } catch (\Exception $exception) {
            $this->application->renderException($exception, $output);
        } finally {
            $this->container->restore($outerInput);
            $this->container->restore($outerOutput);
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
        foreach ($this->tokenizer->getClasses(SymfonyCommand::class, null, 'Command') as $class) {
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
     */
    public function handleSnapshot(SnapshotInterface $snapshot)
    {
        $this->application()->renderException($snapshot->getException(), new ConsoleOutput());
    }
}