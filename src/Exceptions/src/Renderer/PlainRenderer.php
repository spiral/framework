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

        $exceptions = \array_reverse($exceptions);

        $result = [];
        $rootDir = \getcwd();

        foreach ($exceptions as $exception) {
            $file = \str_starts_with($exception->getFile(), $rootDir)
                ? \substr($exception->getFile(), \strlen($rootDir) + 1)
                : $exception->getFile();

            $row = \sprintf(
                "[%s]\n%s in %s:%s\n",
                $exception::class,
                $exception->getMessage(),
                $file,
                $exception->getLine(),
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
        if ($stacktrace === []) {
            return '';
        }

        $result = "\n";
        $rootDir = \getcwd();

        $pad = \strlen((string)\count($stacktrace));

        foreach ($stacktrace as $i => $trace) {
            if (isset($trace['type'], $trace['class'])) {
                $line = \sprintf(
                    '%s. %s%s%s()',
                    \str_pad((string)((int) $i + 1), $pad, ' ', \STR_PAD_LEFT),
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            } else {
                $line = $trace['function'];
            }

            if (isset($trace['file'])) {
                $file = (string) $trace['file'];
                \str_starts_with($file, $rootDir) and $file = \substr($file, \strlen($rootDir) + 1);

                $line .= \sprintf(' at %s:%s', $file, $trace['line']);
            }

            if (\in_array($line, $this->lines, true)) {
                continue;
            }

            $this->lines[] = $line;

            $result .= $line . "\n";

            if ($h !== null && !empty($trace['file']) && \is_file($trace['file'])) {
                $str = @\file_get_contents($trace['file']);
                $result .= $h->highlightLines(
                    $str,
                    $trace['line'],
                    self::SHOW_LINES
                ) . "\n";
                unset($str);
            }
        }

        return $result . "\n";
    }
}
