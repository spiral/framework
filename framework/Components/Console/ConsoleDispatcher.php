<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Console;

use Spiral\Components\Debug\Snapshot;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Spiral\Core\DispatcherInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDispatcher extends Component implements DispatcherInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'console';

    /**
     * Tokenizer component.
     *
     * @var Tokenizer
     */
    protected $tokenizer = null;

    /**
     * Console application instance.
     *
     * @var ConsoleApplication
     */
    protected $application = null;

    /**
     * Core to cache found commands.
     *
     * @var Core
     */
    protected $core = null;

    /**
     * Cached list of all existed commands.
     *
     * @var array
     */
    protected $commands = array();

    /**
     * ConsoleDispatcher.
     *
     * @param Tokenizer $tokenizer
     * @param Core      $core
     */
    public function __construct(Tokenizer $tokenizer, Core $core)
    {
        $this->tokenizer = $tokenizer;
        $this->core = $core;
        $this->commands = $core->loadData('commands');

        if (!is_array($this->commands))
        {
            $this->commands = array();
        }
    }

    /**
     * ConsoleApplication instance.
     *
     * @return ConsoleApplication
     */
    public function getApplication()
    {
        if ($this->application)
        {
            return $this->application;
        }

        $this->application = new ConsoleApplication();

        if (!$this->commands)
        {
            $this->findCommands();
        }

        foreach ($this->commands as $command)
        {
            try
            {
                $command = Container::get($command);
                if (method_exists($command, 'isAvailable') && !$command->isAvailable())
                {
                    continue;
                }
            }
            catch (\Exception $exception)
            {
                continue;
            }

            $this->application->add($command);
        }

        return $this->application;
    }

    /**
     * Use tokenizer to find all available command classes, result will be stored in runtime cache to speed up next console
     * call. Command can be called manually to reindex commands.
     */
    public function findCommands()
    {
        $this->commands = array();

        foreach ($this->tokenizer->getClasses('Symfony\Component\Console\Command\Command', null, 'Command') as $class)
        {
            if ($class['abstract'])
            {
                continue;
            }

            $this->commands[] = $class['name'];
        }

        $this->core->saveData('commands', $this->commands);
    }

    /**
     * List of all available command classes.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Letting dispatcher to control application flow and functionality.
     *
     * @param Core $core
     */
    public function start(Core $core)
    {
        $core->loader->setName('loadmap-console');
        $this->getApplication()->run();
    }

    /**
     * Simplified method to perform one command using it's name.
     *
     * @param string               $command    Command name, for example "core:configure".
     * @param array|InputInterface $parameters Command parameters or input interface.
     * @param OutputInterface      $output     Output interface, buffer one will be used if nothing else specified.
     * @return CommandOutput
     * @throws \Exception
     */
    public function command($command, $parameters = array(), OutputInterface $output = null)
    {
        $code = $this->getApplication()->find($command)->run(
            is_object($parameters) ? $parameters : new ArrayInput(compact('command') + $parameters),
            $output = $output ?: new BufferedOutput()
        );

        return CommandOutput::make(array(
            'code'   => $code,
            'output' => $output
        ));
    }

    /**
     * Every dispatcher should know how to handle exception snapshot provided by Debugger.
     *
     * @param Snapshot $snapshot
     * @return mixed
     */
    public function handleException(Snapshot $snapshot)
    {
        $this->getApplication()->renderException($snapshot->getException(), new ConsoleOutput());
    }
}