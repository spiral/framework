<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Console;

use Interop\Container\ContainerInterface;
use Spiral\Console\Helpers\AskHelper;
use Spiral\Core\ResolverInterface;
use Spiral\Core\Traits\SharedTrait;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Basic application command class. Implements method injections and simplified access to
 * container bindings.
 */
abstract class Command extends SymfonyCommand
{
    use SharedTrait;

    /**
     * Command name.
     *
     * @var string
     */
    const NAME = '';

    /**
     * Short command description.
     *
     * @var string
     */
    const DESCRIPTION = '';

    /**
     * Command options specified in Symphony format. For more complex definitions redefine
     * getOptions() method.
     *
     * @var array
     */
    const OPTIONS = [];

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine
     * getArguments() method.
     *
     * @var array
     */
    const ARGUMENTS = [];

    /**
     * OutputInterface is the interface implemented by all Output classes. Only exists when command
     * are being executed.
     *
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * InputInterface is the interface implemented by all input classes. Only exists when command
     * are being executed.
     *
     * @var InputInterface
     */
    protected $input = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        /**
         * Configuring command.
         */
        parent::__construct(static::NAME);
        $this->setDescription(static::DESCRIPTION);

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
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        try {
            list($this->input, $this->output) = [$input, $output];

            return parent::run($input, $output);
        } finally {
            //Scope end
            $this->input = $this->output = null;
        }
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

        /**
         * @var ResolverInterface $resolver
         */
        $resolver = $this->container->get(ResolverInterface::class);

        //Executing perform method with method injection
        return $reflection->invokeArgs(
            $this,
            $resolver->resolveArguments($reflection, compact('input', 'output'))
        );
    }

    /**
     * Define command options.
     *
     * @return array
     */
    protected function defineOptions(): array
    {
        return static::OPTIONS;
    }

    /**
     * Define command arguments.
     *
     * @return array
     */
    protected function defineArguments(): array
    {
        return static::ARGUMENTS;
    }

    /**
     * @return ContainerInterface
     */
    protected function iocContainer(): ContainerInterface
    {
        //We have to always be executed in a container scope
        return $this->container;
    }

    /**
     * Check if verbosity level of output is higher or equal to VERBOSITY_VERBOSE.
     *
     * @return bool
     */
    protected function isVerbosity(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write($messages, bool $newline = false)
    {
        return $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function writeln($messages)
    {
        return $this->output->writeln($messages);
    }

    /**
     * Input option.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function option(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Input argument.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function argument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * Table helper instance with configured header and pre-defined set of rows.
     *
     * @param array  $headers
     * @param array  $rows
     * @param string $style
     *
     * @return Table
     */
    protected function table(array $headers, array $rows = [], string $style = 'default'): Table
    {
        $table = new Table($this->output);

        return $table->setHeaders($headers)->setRows($rows)->setStyle($style);
    }

    /**
     * Create or use cached instance of AskHelper.
     *
     * @return AskHelper
     */
    protected function ask(): AskHelper
    {
        return new AskHelper($this->getHelper('question'), $this->input, $this->output);
    }
}