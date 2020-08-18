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
        $result = '';

        if ($e instanceof \Error) {
            $result .= '[' . get_class($e) . "]\n" . $e->getMessage();
        } else {
            $result .= '[' . get_class($e) . "]\n" . $e->getMessage();
        }

        $result .= sprintf("in %s:%s\n", $e->getFile(), $e->getLine());

        if ($verbosity >= self::VERBOSITY_DEBUG) {
            $result .= $this->renderTrace($e, new Highlighter(new PlainStyle()));
        } elseif ($verbosity >= self::VERBOSITY_VERBOSE) {
            $result .= $this->renderTrace($e);
        }

        return $result;
    }

    /**
     * Render exception call stack.
     *
     * @param \Throwable       $e
     * @param Highlighter|null $h
     * @return string
     */
    private function renderTrace(\Throwable $e, Highlighter $h = null): string
    {
        $stacktrace = $this->getStacktrace($e);
        if (empty($stacktrace)) {
            return '';
        }

        $result = "\nException Trace:\n";

        foreach ($stacktrace as $trace) {
            if (isset($trace['type']) && isset($trace['class'])) {
                $line = sprintf(
                    ' %s%s%s()',
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            } else {
                $line = $trace['function'];
            }

            if (isset($trace['file'])) {
                $line .= sprintf(' at %s:%s', $trace['file'], $trace['line']);
            } else {
                $line .= sprintf(' at %s:%s', 'n/a', 'n/a');
            }

            $result .= $line . "\n";

            if (!empty($h) && !empty($trace['file'])) {
                $result .= $h->highlightLines(
                    file_get_contents($trace['file']),
                    $trace['line'],
                    static::SHOW_LINES
                ) . "\n";
            }
        }

        return $result;
    }
}
