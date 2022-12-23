<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Spiral\Exceptions\Style\PlainStyle;
use Spiral\Exceptions\Verbosity;

final class PlainRenderer extends AbstractRenderer
{
    protected const FORMATS = ['text/plain', 'text', 'plain', 'cli', 'console'];
    // Lines to show around targeted line.
    private const SHOW_LINES = 2;

    private array $lines = [];

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = null,
        string $format = null
    ): string {
        $verbosity ??= $this->defaultVerbosity;
        $exceptions = [$exception];
        while ($exception = $exception->getPrevious()) {
            $exceptions[] = $exception;
        }

        $result = [];

        foreach ($exceptions as $exception) {
            $row = \sprintf(
                "[%s]\n%s in %s:%s\n",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );

            if ($verbosity->value >= Verbosity::DEBUG->value) {
                $row .= $this->renderTrace($exception, new Highlighter(new PlainStyle()));
            } elseif ($verbosity->value >= Verbosity::VERBOSE->value) {
                $row .= $this->renderTrace($exception);
            }

            $result[] = $row;
        }

        $this->lines = [];

        return \implode('', \array_reverse($result));
    }

    /**
     * Render exception call stack.
     */
    private function renderTrace(\Throwable $e, Highlighter $h = null): string
    {
        $stacktrace = $this->getStacktrace($e);
        if (empty($stacktrace)) {
            return '';
        }

        $result = "\n";

        foreach ($stacktrace as $i => $trace) {
            if (isset($trace['type'], $trace['class'])) {
                $line = \sprintf(
                    '%s %s%s%s()',
                    '#'.$i,
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            } else {
                $line = $trace['function'];
            }

            if (isset($trace['file'])) {
                $line .= \sprintf(' at %s:%s', $trace['file'], $trace['line']);
            } else {
                $line .= \sprintf(' at %s:%s', 'n/a', 'n/a');
            }

            if (\in_array($line, $this->lines, true)) {
                continue;
            }

            $this->lines[] = $line;

            $result .= $line . "\n";

            if ($h !== null && !empty($trace['file'])) {
                $result .= $h->highlightLines(
                        \file_get_contents($trace['file']),
                        $trace['line'],
                        self::SHOW_LINES
                    ) . "\n";
            }
        }

        return $result . "\n";
    }
}
