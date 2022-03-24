<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

use Codedungeon\PHPCliColors\Color;
use Spiral\Debug\System;
use Spiral\Exceptions\Style\ConsoleStyle;
use Spiral\Exceptions\Style\PlainStyle;

/**
 * Verbosity levels:
 *
 * 1) BASIC   - only message header and line number
 * 2) VERBOSE - stack information
 * 3) DEBUG   - stack and source information.
 */
class ConsoleHandler extends AbstractHandler
{
    // Lines to show around targeted line.
    public const SHOW_LINES = 2;

    protected const COLORS = [
        'bg:red'     => Color::BG_RED,
        'bg:cyan'    => Color::BG_CYAN,
        'bg:magenta' => Color::BG_MAGENTA,
        'bg:white'   => Color::BG_WHITE,
        'white'      => Color::LIGHT_WHITE,
        'green'      => Color::GREEN,
        'black'      => Color::BLACK,
        'red'        => Color::RED,
        'yellow'     => Color::YELLOW,
        'reset'      => Color::RESET,
    ];

    /** @var StyleInterface */
    private $colorsSupport;

    /**
     * @param bool|resource $stream
     */
    public function __construct($stream = STDOUT)
    {
        $this->colorsSupport = System::isColorsSupported($stream);
    }

    /**
     * Disable or enable colorization support.
     */
    public function setColorsSupport(bool $enabled = true): void
    {
        $this->colorsSupport = $enabled;
    }

    /**
     * @inheritdoc
     */
    public function renderException(\Throwable $e, int $verbosity = self::VERBOSITY_BASIC): string
    {
        $exceptions = [];
        while ($prev = $e->getPrevious()) {
            $exceptions[] = $this->renderFormatted($prev);
        }
        $result = \implode("\n", \array_merge(\array_reverse($exceptions), [$this->renderFormatted($e)]));

        if ($verbosity >= self::VERBOSITY_DEBUG) {
            return $result . $this->renderTrace($e, new Highlighter(
                $this->colorsSupport ? new ConsoleStyle() : new PlainStyle()
            ));
        }
        if ($verbosity >= self::VERBOSITY_VERBOSE) {
            return $result . $this->renderTrace($e);
        }

        return $result;
    }

    /**
     * Render title using outlining border.
     *
     * @param string $title Title.
     * @param string $style Formatting.
     */
    private function renderHeader(string $title, string $style, int $padding = 0): string
    {
        $result = '';

        $lines = \explode("\n", \str_replace("\r", '', $title));

        $length = 0;
        \array_walk($lines, static function ($v) use (&$length): void {
            $length = \max($length, \mb_strlen($v));
        });

        $length += $padding;

        foreach ($lines as $line) {
            $result .= $this->format(
                "<{$style}>%s%s%s</reset>\n",
                \str_repeat(' ', $padding + 1),
                $line,
                \str_repeat(' ', $length - \mb_strlen($line) + 1)
            );
        }

        return $result;
    }

    /**
     * Render exception call stack.
     *
     * @param Highlighter|null $h
     */
    private function renderTrace(\Throwable $e, Highlighter $h = null): string
    {
        $stacktrace = $this->getStacktrace($e);
        if (empty($stacktrace)) {
            return '';
        }

        $result = $this->format("\n<red>Exception Trace:</reset>\n");

        foreach ($stacktrace as $trace) {
            $line = isset($trace['type'], $trace['class'])
                ? $this->format(
                    ' <white>%s%s%s()</reset>',
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                )
                : $this->format(
                    ' <white>%s()</reset>',
                    $trace['function']
                );

            if (isset($trace['file'])) {
                $line .= $this->format(
                    ' <yellow>at</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>',
                    $trace['file'],
                    $trace['line']
                );
            } else {
                $line .= $this->format(
                    ' <yellow>at</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>',
                    'n/a',
                    'n/a'
                );
            }

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
     * Format string and apply color formatting (if enabled).
     *
     * @param mixed  ...$args
     */
    private function format(string $format, ...$args): string
    {
        if (!$this->colorsSupport) {
            $format = \preg_replace('/<[^>]+>/', '', $format);
        } else {
            $format = \preg_replace_callback('/(<([^>]+)>)/', static function ($partial) {
                $style = '';
                foreach (\explode(',', \trim($partial[2], '/')) as $color) {
                    if (isset(self::COLORS[$color])) {
                        $style .= self::COLORS[$color];
                    }
                }

                return $style;
            }, $format);
        }

        return \sprintf($format, ...$args);
    }

    /**
     * Convert exception to a formatted string.
     */
    private function renderFormatted(\Throwable $e): string
    {
        return $this->renderHeader(
            \sprintf("[%s]\n%s", \get_class($e), $e->getMessage()),
            $e instanceof \Error ? 'bg:magenta,white' : 'bg:red,white'
        ) . $this->format(
            '<yellow>in</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>',
            $e->getFile(),
            $e->getLine()
        );
    }
}
