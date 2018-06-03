<?php
/**
 * framework
 *
 * @author    Wolfy-J
 */

namespace Spiral\Console;

use Spiral\Database\Exceptions\QueryExceptionInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class ErrorWriter
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Terminal
     */
    private $terminal;

    /**
     * @param Terminal $terminal
     */
    public function __construct(Terminal $terminal)
    {
        $this->terminal = $terminal;
    }

    public function renderException(OutputInterface $output, \Throwable $e)
    {
        $this->output = $output;

        try {
            do {
                $this->renderHeader($e);
                if ($this->output->isVerbose()) {
                    $output->writeln('');
                    $this->renderStack($e);
                }

                $output->writeln('');
            } while ($e = $e->getPrevious());
        } finally {
            $this->output = null;
        }
    }

    /**
     * Render exception header.
     *
     * @param \Throwable $e
     */
    private function renderHeader(\Throwable $e)
    {
        if ($e instanceof \Error) {
            $this->title("[" . get_class($e) . "]\n" . $e->getMessage(), "bg=magenta;fg=white");
        } else {
            $this->title("[" . get_class($e) . "]\n" . $e->getMessage(), "bg=red;fg=white");
        }

        if ($e instanceof QueryExceptionInterface) {
            $this->title($e->getQuery(), "bg=blue;fg=white");
        }

        $this->write(
            "<fg=yellow>in <fg=white>%s</fg=white> at line <fg=white>%s</fg=white></fg=yellow>",
            basename($e->getFile()),
            $e->getLine()
        );
    }

    /**
     * Render exception call stack.
     *
     * @param \Throwable $e
     */
    private function renderStack(\Throwable $e)
    {
        $stacktrace = $e->getTrace();

        $this->write("<fg=yellow>Exception trace:</fg=yellow>");

        // ???
        $header = ['file' => $e->getFile(), 'line' => $e->getLine()] + $stacktrace[0];
        if ($stacktrace[0] != $header) {
            array_unshift($stacktrace, $header);
        }

        foreach ($stacktrace as $trace) {
            if (isset($trace['type']) && isset($trace['class'])) {
                $line = sprintf(
                    " %s%s%s()",
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            } else {
                $line = sprintf(
                    " %s()",
                    $trace['function']
                );
            }

            if (isset($trace['file'])) {
                $line .= sprintf(
                    " <fg=yellow>at</fg=yellow> <fg=green>%s:<fg=yellow>%d</fg=yellow></fg=green>",
                    $trace['file'],
                    $trace['line']
                );
            } else {
                $line .= " <fg=yellow>at</fg=yellow> <fg=green>n/a:<fg=yellow>n/a</fg=yellow></fg=green>";
            }

            $this->write($line);
        }
    }

    /**
     * Render title using outlining border.
     *
     * @param string $title Title.
     * @param string $f     Formatting.
     */
    private function title($title, $f)
    {
        $lines = explode("\n", str_replace("\r", "", $title));
        $length = 0;
        array_walk($lines, function ($v) use (&$length) {
            $length = max($length, mb_strlen($v));
        });

        $this->write("<$f>%s</$f>", str_repeat(" ", $length + 2));
        foreach ($lines as $line) {
            $this->write(
                "<$f> %s%s</$f>",
                $line,
                str_repeat(" ", $length - mb_strlen($line) + 1)
            );

        }
        $this->write("<$f>%s</$f>", str_repeat(" ", $length + 2));
    }

    /**
     * Write formatted line.
     *
     * @param string $format
     * @param array  ...$args
     */
    private function write($format, ... $args)
    {
        $this->output->writeln(sprintf($format, ...$args), OutputInterface::VERBOSITY_QUIET);
    }
}