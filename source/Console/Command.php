<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Console;

use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Components;
use Spiral\Core\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Spiral Application specific bindings.
 *
 * @property \Spiral\Core\Core                  $core
 * @property \Spiral\Core\Loader                $loader
 * @property \Spiral\Modules\ModuleManager      $modules
 *
 * @property \Spiral\Console\ConsoleDispatcher  $console
 * @property \Spiral\Http\HttpDispatcher        $http
 *
 * @property \Spiral\Cache\CacheManager         $cache
 * @property \Spiral\Http\Cookies\CookieManager $cookies
 * @property \Spiral\Database\DatabaseManager   $dbal
 * @property \Spiral\Encrypter\Encrypter        $encrypter
 * @property \Spiral\Files\FileManager          $files
 * @property \Spiral\ODM\ODM                    $odm
 * @property \Spiral\ORM\ORM                    $orm
 * @property \Spiral\Session\SessionStore       $session
 * @property \Spiral\Tokenizer\Tokenizer        $tokenizer
 * @property \Spiral\Translator\Translator      $i18n
 * @property \Spiral\Views\ViewManager          $view
 *
 * @property \Spiral\Redis\RedisManager         $redis
 * @property \Spiral\Image\ImageManager         $image
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Associated container.
     *
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine
     * getOptions() method.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine
     * getArguments() method.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * OutputInterface is the interface implemented by all Output classes.
     *
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * InputInterface is the interface implemented by all input classes.
     *
     * @var InputInterface
     */
    protected $input = null;

    /**
     * Ask helper, holds question functions.
     *
     * @var AskHelper
     */
    protected $ask = null;

    /**
     * Constructing new command class. Method will perform simplified command initialization.
     */
    public function __construct()
    {
        parent::__construct($this->name);
        $this->setDescription($this->description);

        foreach ($this->getOptions() as $option)
        {
            call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->getArguments() as $argument)
        {
            call_user_func_array([$this, 'addArgument'], $argument);
        }
    }

    /**
     * Sets the application instance for this command.
     *
     * @param Application $application An Application instance
     *
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        if (!is_null($application) && $application instanceof ConsoleApplication)
        {
            $this->container = $application->getContainer();
        }
    }

    /**
     * Command container.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Should indicate if command available for console application, method can return false if
     * parent modules not installed
     * and etc.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Command options. By default "options" property will be used.
     *
     * @return array
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Command arguments. By default "arguments" property will be used.
     *
     * @return array
     */
    protected function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Check if additional debug information is required.
     *
     * @return bool
     */
    protected function isVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Get input option.
     *
     * @param string $name
     * @return mixed
     */
    protected function option($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Get input argument.
     *
     * @param string $name
     * @return mixed
     */
    protected function argument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @throws \InvalidArgumentException When unknown output type is given
     */
    public function write($messages, $newline = false)
    {
        return $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @throws \InvalidArgumentException When unknown output type is given
     */
    public function writeln($messages)
    {
        return $this->output->writeln($messages);
    }

    /**
     * Runs the command. The code to execute is either defined directly with the setCode() method
     * or by overriding the execute() method in a sub-class.
     *
     * @see setCode()
     * @see execute()
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return int The command exit code
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        //We can refill internal options as we don't need them at this stage
        $this->options = $this->input->getOptions();
        $this->arguments = $this->input->getArguments();

        $this->ask = new AskHelper($this->getHelper('question'), $input, $output);

        return parent::run($input, $output);
    }

    /**
     * Executes the current command.
     * This method is not abstract because you can use this class as a concrete class. In this case,
     * instead of defining the execute() method, you set the code to execute by passing a Closure to
     * the setCode() method.
     *
     * Method will pass call to perform() method with DI.
     *
     * @see setCode()
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reflection = new \ReflectionMethod($this, 'perform');
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($this, $this->container->resolveArguments(
            $reflection,
            compact('input', 'output')
        ));
    }

    /**
     * Get table builder with specified type and headers.
     *
     * @param array  $headers Column header.
     * @param array  $rows    Pre-defined set of rows.
     * @param string $style
     * @return Table
     */
    public function createTable(array $headers, $rows = [], $style = 'default')
    {
        return (new Table($this->output))->setHeaders($headers)->setRows($rows)->setStyle($style);
    }

    /**
     * An alias for Container::getInstance()->get() method to retrieve components by their alias.
     *
     * @param string $name Binding or component name/alias.
     * @return Component
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }
}