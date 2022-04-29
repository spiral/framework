<?php

declare(strict_types=1);

namespace Spiral\Console\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait expect command to set $output and $input scopes.
 */
trait HelpersTrait
{
    /**
     * OutputInterface is the interface implemented by all Output classes. Only exists when command
     * are being executed.
     */
    protected ?OutputInterface $output = null;

    /**
     * InputInterface is the interface implemented by all input classes. Only exists when command
     * are being executed.
     */
    protected ?InputInterface $input = null;

    /**
     * Check if verbosity level of output is higher or equal to VERBOSITY_VERBOSE.
     */
    protected function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Input option.
     */
    protected function option(string $name): mixed
    {
        return $this->input->getOption($name);
    }

    /**
     * Input argument.
     */
    protected function argument(string $name): mixed
    {
        return $this->input->getArgument($name);
    }

    /**
     * Identical to write function but provides ability to format message. Does not add new line.
     */
    protected function sprintf(string $format, mixed ...$args): void
    {
        $this->output->write(\sprintf($format, ...$args), false);
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write(string|iterable $messages, bool $newline = false): void
    {
        $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|iterable<mixed, string> $messages The message as an array of lines of a single string
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function writeln(string|iterable $messages): void
    {
        $this->output->writeln($messages);
    }

    /**
     * Table helper instance with configured header and pre-defined set of rows.
     */
    protected function table(array $headers, array $rows = [], string $style = 'default'): Table
    {
        $table = new Table($this->output);

        return $table->setHeaders($headers)->setRows($rows)->setStyle($style);
    }
}
