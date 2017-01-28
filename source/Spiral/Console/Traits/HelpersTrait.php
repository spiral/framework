<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Console\Traits;

use Spiral\Console\Helpers\AskHelper;
use Symfony\Component\Console\Helper\Table;

/**
 * Table and AskHelper shortcuts.
 */
trait HelpersTrait
{
    /**
     * OutputInterface is the interface implemented by all Output classes. Only exists when command
     * are being executed.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output = null;

    /**
     * InputInterface is the interface implemented by all input classes. Only exists when command
     * are being executed.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input = null;

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

    /**
     * @return \Interop\Container\ContainerInterface
     */
    abstract protected function iocContainer();
}