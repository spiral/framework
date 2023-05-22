<?php

declare(strict_types=1);

namespace Spiral\Console\Traits;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Trait expect command to set $output and $input scopes.
 */
trait HelpersTrait
{
    /**
     * OutputInterface is the interface implemented by all Output classes. Only exists when command
     * are being executed.
     * @var SymfonyStyle|null
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
     * Determine if the input option is present.
     */
    protected function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    /**
     * Input option.
     */
    protected function option(string $name): mixed
    {
        return $this->input->getOption($name);
    }

    /**
     * Determine if the input argument is present.
     */
    protected function hasArgument(string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Input argument.
     */
    protected function argument(string $name): mixed
    {
        return $this->input->getArgument($name);
    }

    /**
     * Asks for confirmation.
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Asks a question.
     */
    protected function ask(string $question, string $default = null): mixed
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Asks a multiple choice question.
     */
    protected function choiceQuestion(
        string $question,
        array $choices,
        mixed $default = null,
        int $attempts = null,
        bool $multiselect = false
    ): mixed {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiselect);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     */
    protected function secret(string $question, bool $fallback = true): mixed
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Write a string as information output.
     */
    protected function info(string $string): void
    {
        $this->line($string, 'info');
    }

    /**
     * Write a string as comment output.
     */
    protected function comment(string $string): void
    {
        $this->line($string, 'comment');
    }

    /**
     * Write a string as question output.
     */
    protected function question(string $string): void
    {
        $this->line($string, 'question');
    }

    /**
     * Write a string as error output.
     */
    protected function error(string $string): void
    {
        $this->line($string, 'error');
    }

    /**
     * Write a string as warning output.
     */
    protected function warning(string $string): void
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning');
    }

    /**
     * Write a string in an alert box.
     */
    protected function alert(string $string): void
    {
        $length = \mb_strlen(\strip_tags($string)) + 12;
        $stringLines = explode("\n", wordwrap($string, 300));

        $this->comment(\str_repeat('*', $length));
        foreach ($stringLines as $line) {
            $this->comment('*     ' . $line . '     *');
        }
        $this->comment(\str_repeat('*', $length));

        $this->newLine();
    }

    /**
     * Write a string as standard output.
     */
    protected function line(string $string, string $style = null): void
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->writeln(
            messages: $styled
        );
    }

    /**
     * Write a blank line.
     */
    protected function newLine(int $count = 1): void
    {
        $this->output->newLine($count);
    }

    /**
     * Identical to write function but provides ability to format message. Does not add new line.
     */
    protected function sprintf(string $format, mixed ...$args): void
    {
        $this->write(
            messages: \sprintf($format, ...$args),
            newline: false
        );
    }

    /**
     * Writes a message to the output.
     *
     * @param string|iterable $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write(string|iterable $messages, bool $newline = false): void
    {
        $this->output->write(messages: $messages, newline: $newline);
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
        $this->output->writeln(messages: $messages);
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
