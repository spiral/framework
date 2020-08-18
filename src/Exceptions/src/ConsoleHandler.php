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
     *
     * @param bool $enabled
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
        $result = '';

        if ($e instanceof \Error) {
            $result .= $this->renderHeader('[' . get_class($e) . "]\n" . $e->getMessage(), 'bg:magenta,white');
        } else {
            $result .= $this->renderHeader('[' . get_class($e) . "]\n" . $e->getMessage(), 'bg:red,white');
        }

        $result .= $this->format(
            "<yellow>in</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>\n",
            $e->getFile(),
            $e->getLine()
        );

        if ($verbosity >= self::VERBOSITY_DEBUG) {
            $result .= $this->renderTrace($e, new Highlighter(
                $this->colorsSupport ? new ConsoleStyle() : new PlainStyle()
            ));
        } elseif ($verbosity >= self::VERBOSITY_VERBOSE) {
            $result .= $this->renderTrace($e);
        }

        return $result;
    }

    /**
     * Render title using outlining border.
     *
     * @param string $title Title.
     * @param string $style Formatting.
     * @param int    $padding
     * @return string
     */
    private function renderHeader(string $title, string $style, int $padding = 0): string
    {
        $result = '';

        $lines = explode("\n", str_replace("\r", '', $title));

        $length = 0;
        array_walk($lines, function ($v) use (&$length): void {
            $length = max($length, mb_strlen($v));
        });

        $length += $padding;

        foreach ($lines as $line) {
            $result .= $this->format(
                "<{$style}>%s%s%s</reset>\n",
                str_repeat(' ', $padding + 1),
                $line,
                str_repeat(' ', $length - mb_strlen($line) + 1)
            );
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

        $result = $this->format("\n<red>Exception Trace:</reset>\n");

        foreach ($stacktrace as $trace) {
            if (isset($trace['type']) && isset($trace['class'])) {
                $line = $this->format(
                    ' <white>%s%s%s()</reset>',
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            } else {
                $line = $this->format(
                    ' <white>%s()</reset>',
                    $trace['function']
                );
            }

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

    /**
     * Format string and apply color formatting (if enabled).
     *
     * @param string $format
     * @param mixed  ...$args
     * @return string
     */
    private function format(string $format, ...$args): string
    {
        if (!$this->colorsSupport) {
            $format = preg_replace('/<[^>]+>/', '', $format);
        } else {
            $format = preg_replace_callback('/(<([^>]+)>)/', function ($partial) {
                $style = '';
                foreach (explode(',', trim($partial[2], '/')) as $color) {
                    if (isset(self::COLORS[$color])) {
                        $style .= self::COLORS[$color];
                    }
                }

                return $style;
            }, $format);
        }

        return sprintf($format, ...$args);
    }
}
