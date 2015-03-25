<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Console;

use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Spiral\Core\Loader;
use Spiral\Components;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property Core                                 $core
 * @property Components\Http\HttpDispatcher       $http
 * @property Components\Console\ConsoleDispatcher $console
 * @property Loader                               $loader
 * @property Components\Modules\ModuleManager     $modules
 * @property Components\Files\FileManager         $file
 * @property Components\Debug\Debugger            $debug
 * @property Components\Tokenizer\Tokenizer       $tokenizer
 * @property Components\Cache\CacheManager        $cache
 * @property Components\I18n\Translator  $i18n
 * @property Components\View\ViewManager                 $view
 * @property Components\Redis\RedisManager        $redis
 * @property Components\Encrypter\Encrypter       $encrypter
 * @property Components\Image\ImageManager        $image
 * @property Components\Storage\StorageManager    $storage
 * @property Components\DBAL\DatabaseManager      $dbal
 * @property Components\ORM\ORM                   $orm
 * @property Components\ODM\ODM                   $odm
 */
abstract class Command extends BaseCommand
{
    /**
     * Calling method with dependencies.
     */
    use Container\MethodTrait;

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
    protected $options = array();

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine
     * getArguments() method.
     *
     * @var array
     */
    protected $arguments = array();

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
            call_user_func_array(array($this, 'addOption'), $option);
        }

        foreach ($this->getArguments() as $argument)
        {
            call_user_func_array(array($this, 'addArgument'), $argument);
        }
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

        $this->ask = AskHelper::make(array(
            'helper' => $this->getHelper('question'),
            'input'  => $input,
            'output' => $output
        ));

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
        $this->callMethod('perform', compact('input', 'output'));
    }

    /**
     * Get table builder with specified type and headers.
     *
     * @param array  $headers Column header.
     * @param array  $rows    Pre-defined set of rows.
     * @param string $style
     * @return Table
     */
    public function table(array $headers, $rows = array(), $style = 'default')
    {
        return (new Table($this->output))->setHeaders($headers)->setRows($rows)->setStyle($style);
    }

    /**
     * An alias for Container::get() method to retrieve components by their alias.
     *
     * @param string $name Binding or component name/alias.
     * @return Component
     */
    public function __get($name)
    {
        return Container::get($name);
    }
}