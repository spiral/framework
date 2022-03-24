<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

use Spiral\Exceptions\Style\PlainStyle;

final class PlainHandler extends AbstractHandler
{
    // Lines to show around targeted line.
    private const SHOW_LINES = 2;

    /**
     * @inheritdoc
     */
    public function renderException(\Throwable $e, int $verbosity = self::VERBOSITY_BASIC): string
    {
        $exceptions = [$this->renderFormatted($e)];
        for ($prev = $e->getPrevious(); $prev !== null; $prev = $prev->getPrevious()) {
            $exceptions[] = $this->renderFormatted($prev);
        }
        $result = \implode("\n", $exceptions);

        if ($verbosity >= self::VERBOSITY_DEBUG) {
            $result .= $this->renderTrace($e, new Highlighter(new PlainStyle()));
        } elseif ($verbosity >= self::VERBOSITY_VERBOSE) {
            $result .= $this->renderTrace($e);
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
            $line = isset($trace['type'], $trace['class'])
                ? \sprintf(
                    ' %s%s%s()',
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                )
                : $trace['function'];

            $line .= isset($trace['file'])
                ? \sprintf(' at %s:%s', $trace['file'], $trace['line'])
                : \sprintf(' at %s:%s', 'n/a', 'n/a');

            $result .= $line . "\n";

            if ($h !== null && !empty($trace['file'])) {
                $result .= $h->highlightLines(
                    \file_get_contents($trace['file']),
                    $trace['line'],
                    static::SHOW_LINES
                ) . "\n";
            }
        }

        return $result;
    }

    /**
     * Convert exception to a formatted string.
     */
    private function renderFormatted(\Throwable $e): string
    {
        return \sprintf(
            "[%s]\n%s in %s:%s\n",
            \get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }
}
