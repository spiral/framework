<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Console;

use Console\Traits\HelpersTrait;
use Interop\Container\ContainerInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Core\Traits\SharedTrait;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Basic application command class. Implements method injections and simplified access to
 * container bindings.
 */
abstract class Command extends SymfonyCommand
{
    use SharedTrait, HelpersTrait;

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
    protected function iocContainer()
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
}