<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Console;

use Spiral\Console\Helpers\AskHelper;
use Spiral\Core\Container;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Traits\SharedTrait;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Basic application command class. Implements method injections and simplified access to
 * container bindings.
 */
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * Shared stuff.
     */
    use SharedTrait;

    /**
     * Instance of ask helper.
     *
     * @var AskHelper
     */
    private $askHelper = null;

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
     * Configures symfony command based on simplified class definition.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct($this->name);
        $this->setDescription($this->description);

        foreach ($this->defineOptions() as $option) {
            call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            call_user_func_array([$this, 'addArgument'], $argument);
        }
    }

    /**
     * Command can hide itself from parent ConsoleDispatcher when it needed.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        //We can refill internal options as we don't need them at this stage
        $this->options = $this->input->getOptions();
        $this->arguments = $this->input->getArguments();

        return parent::run($input, $output);
    }

    /**
     * {@inheritdoc}
     *
     * Pass execution to "perform" method using container to resolve method dependencies.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reflection = new \ReflectionMethod($this, 'perform');
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($this, $this->container->resolveArguments(
            $reflection, compact('input', 'output')
        ));
    }

    /**
     * @return ContainerInterface
     */
    protected function container()
    {
        return $this->container;
    }

    /**
     * Define command options.
     *
     * @return array
     */
    protected function defineOptions()
    {
        return $this->options;
    }

    /**
     * Define command arguments.
     *
     * @return array
     */
    protected function defineArguments()
    {
        return $this->arguments;
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write($messages, $newline = false)
    {
        return $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function writeln($messages)
    {
        return $this->output->writeln($messages);
    }

    /**
     * Check if verbosity level of output is higher or equal to VERBOSITY_VERBOSE.
     *
     * @return bool
     */
    protected function isVerbosity()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Input option.
     *
     * @param string $name
     * @return mixed
     */
    protected function option($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Input argument.
     *
     * @param string $name
     * @return mixed
     */
    protected function argument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * Table helper instance with configured header and pre-defined set of rows.
     *
     * @param array  $headers
     * @param array  $rows
     * @param string $style
     * @return Table
     */
    protected function tableHelper(array $headers, $rows = [], $style = 'default')
    {
        return (new Table($this->output))->setHeaders($headers)->setRows($rows)->setStyle($style);
    }

    /**
     * Create or use cached instance of AskHelper.
     *
     * @return AskHelper
     */
    protected function ask()
    {
        if (!empty($this->askHelper)) {
            return $this->askHelper;
        }

        return $this->askHelper = new AskHelper(
            $this->getHelper('question'),
            $this->input,
            $this->output
        );
    }
}