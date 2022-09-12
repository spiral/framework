<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Codedungeon\PHPCliColors\Color;
use Spiral\Exceptions\Style\ConsoleStyle;
use Spiral\Exceptions\Style\PlainStyle;
use Spiral\Exceptions\Verbosity;

/**
 * Verbosity levels:
 *
 * 1) {@see Verbosity::BASIC} - only message header and line number
 * 2) {@see Verbosity::VERBOSE} - stack information
 * 3) {@see Verbosity::DEBUG} - stack and source information.
 */
class ConsoleRenderer extends AbstractRenderer
{
    // Lines to show around targeted line.
    public const SHOW_LINES = 2;
    protected const FORMATS = ['console', 'cli'];

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

    private bool $colorsSupport;

    /**
     * @param bool|resource $stream
     */
    public function __construct(mixed $stream = null)
    {
        $stream ??= \defined('\STDOUT') ? '\STDOUT' : \fopen('php://stdout', 'wb');
        $this->colorsSupport = $this->isColorsSupported($stream);
    }

    /**
     * Disable or enable colorization support.
     */
    public function setColorsSupport(bool $enabled = true): void
    {
        $this->colorsSupport = $enabled;
    }

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = null,
        string $format = null
    ): string {
        $verbosity ??= $this->defaultVerbosity;

        $result = $this->renderHeader(
            \sprintf("[%s]\n%s", $exception::class, $exception->getMessage()),
            $exception instanceof \Error ? 'bg:magenta,white' : 'bg:red,white'
        );

        $result .= $this->format(
            "<yellow>in</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>\n",
            $exception->getFile(),
            $exception->getLine()
        );

        if ($verbosity->value >= Verbosity::DEBUG->value) {
            $result .= $this->renderTrace($exception, new Highlighter(
                $this->colorsSupport ? new ConsoleStyle() : new PlainStyle()
            ));
        } elseif ($verbosity->value >= Verbosity::VERBOSE->value) {
            $result .= $this->renderTrace($exception);
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
            $length = max($length, \mb_strlen($v));
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
     */
    private function renderTrace(\Throwable $e, Highlighter $h = null): string
    {
        $stacktrace = $this->getStacktrace($e);
        if (empty($stacktrace)) {
            return '';
        }

        $result = $this->format("\n<red>Exception Trace:</reset>\n");

        foreach ($stacktrace as $trace) {
            if (isset($trace['type'], $trace['class'])) {
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
     */
    private function format(string $format, mixed ...$args): string
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
     * Returns true if the STDOUT supports colorization.
     * @codeCoverageIgnore
     * @link https://github.com/symfony/Console/blob/master/Output/StreamOutput.php#L94
     */
    private function isColorsSupported(mixed $stream = STDOUT): bool
    {
        if ('Hyper' === \getenv('TERM_PROGRAM')) {
            return true;
        }

        try {
            if (\DIRECTORY_SEPARATOR === '\\') {
                return (\function_exists('sapi_windows_vt100_support') && @\sapi_windows_vt100_support($stream))
                    || \getenv('ANSICON') !== false
                    || \getenv('ConEmuANSI') === 'ON'
                    || \getenv('TERM') === 'xterm';
            }

            return @\stream_isatty($stream);
        } catch (\Throwable) {
            return false;
        }
    }
}
