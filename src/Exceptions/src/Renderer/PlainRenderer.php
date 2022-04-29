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

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = null,
        string $format = null
    ): string {
        $verbosity ??= $this->defaultVerbosity;
        $result = \sprintf(
            "[%s]\n%s in %s:%s\n",
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        if ($verbosity->value >= Verbosity::DEBUG->value) {
            $result .= $this->renderTrace($exception, new Highlighter(new PlainStyle()));
        } elseif ($verbosity->value >= Verbosity::VERBOSE->value) {
            $result .= $this->renderTrace($exception);
        }

        return $result;
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

        $result = "\nException Trace:\n";

        foreach ($stacktrace as $trace) {
            if (isset($trace['type'], $trace['class'])) {
                $line = \sprintf(
                    ' %s%s%s()',
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

            $result .= $line . "\n";

            if ($h !== null && !empty($trace['file'])) {
                $result .= $h->highlightLines(
                    \file_get_contents($trace['file']),
                    $trace['line'],
                    self::SHOW_LINES
                ) . "\n";
            }
        }

        return $result;
    }
}
