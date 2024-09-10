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
        'bg:red' => Color::BG_RED,
        'bg:cyan' => Color::BG_CYAN,
        'bg:magenta' => Color::BG_MAGENTA,
        'bg:white' => Color::BG_WHITE,
        'white' => Color::LIGHT_WHITE,
        'green' => Color::GREEN,
        'gray' => Color::GRAY,
        'black' => Color::BLACK,
        'red' => Color::RED,
        'yellow' => Color::YELLOW,
        'reset' => Color::RESET,
    ];

    private array $lines = [];

    private bool $colorsSupport;

    /**
     * @param bool|resource|null $stream
     */
    public function __construct(mixed $stream = null)
    {
        $stream ??= \defined('\STDOUT') ? \STDOUT : \fopen('php://stdout', 'wb');

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

        $exceptions = [$exception];
        $currentE = $exception;

        while ($exception = $exception->getPrevious()) {
            $exceptions[] = $exception;
        }

        $exceptions = \array_reverse($exceptions);

        $result = [];
        $rootDir = \getcwd();

        foreach ($exceptions as $exception) {
            $prefix = $currentE === $exception ? '' : 'Previous: ';
            $row = $this->renderHeader(
                \sprintf("%s[%s]\n%s", $prefix, $exception::class, $exception->getMessage()),
                $exception instanceof \Error ? 'bg:magenta,white' : 'bg:red,white'
            );

            $file = \str_starts_with($exception->getFile(), $rootDir)
                ? \substr($exception->getFile(), \strlen($rootDir) + 1)
                : $exception->getFile();

            $row .= $this->format(
                "<yellow>in</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>\n",
                $file,
                $exception->getLine()
            );

            if ($verbosity->value >= Verbosity::DEBUG->value) {
                $row .= $this->renderTrace(
                    $exception,
                    new Highlighter(
                        $this->colorsSupport ? new ConsoleStyle() : new PlainStyle()
                    )
                );
            } elseif ($verbosity->value >= Verbosity::VERBOSE->value) {
                $row .= $this->renderTrace($exception);
            }

            $result[] = $row;
        }

        $this->lines = [];

        return \implode("\n", \array_reverse($result));
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
                \str_repeat('', $padding + 1),
                $line,
                \str_repeat('', $length - \mb_strlen($line) + 1)
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

        $result = "\n";
        $rootDir = \getcwd();

        $pad = \strlen((string)\count($stacktrace));

        foreach ($stacktrace as $i => $trace) {
            $file = isset($trace['file']) ? (string) $trace['file'] : null;
            $classColor = 'while';

            if ($file !== null) {
                \str_starts_with($file, $rootDir) and $file = \substr($file, \strlen($rootDir) + 1);
                $classColor = \str_starts_with($file, 'vendor/') ? 'gray' : 'white';
            }

            if (isset($trace['type'], $trace['class'])) {
                $line = $this->format(
                    "<$classColor>%s.</reset> <white>%s%s%s()</reset>",
                    \str_pad((string)((int)$i + 1), $pad, ' ', \STR_PAD_LEFT),
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
            if ($file !== null) {
                $line .= $this->format(
                    ' <yellow>at</reset> <green>%s</reset><yellow>:</reset><white>%s</reset>',
                    $file,
                    $trace['line']
                );
            }

            if (\in_array($line, $this->lines, true)) {
                continue;
            }

            $this->lines[] = $line;

            $result .= $line . "\n";

            if ($h !== null && !empty($trace['file'])) {
                $str = @\file_get_contents($trace['file']);
                $result .= $h->highlightLines(
                    $str,
                    $trace['line'],
                    static::SHOW_LINES
                ) . "\n";
                unset($str);
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
