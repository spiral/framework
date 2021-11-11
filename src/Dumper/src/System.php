<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug;

/**
 * Describes the env PHP script is running within.
 */
final class System
{
    /**
     * Return true if PHP running in CLI mode.
     *
     * @codeCoverageIgnore
     */
    public static function isCLI(): bool
    {
        if (!empty(getenv('RR'))) {
            // Do not treat RoadRunner as CLI.
            return false;
        }

        if (php_sapi_name() === 'cli') {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the STDOUT supports colorization.
     *
     * @codeCoverageIgnore
     * @link https://github.com/symfony/Console/blob/master/Output/StreamOutput.php#L94
     * @param mixed $stream
     */
    public static function isColorsSupported($stream = STDOUT): bool
    {
        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }

        try {
            if (\DIRECTORY_SEPARATOR === '\\') {
                return (
                        function_exists('sapi_windows_vt100_support')
                        && @sapi_windows_vt100_support($stream)
                    ) || getenv('ANSICON') !== false
                    || getenv('ConEmuANSI') == 'ON'
                    || getenv('TERM') == 'xterm';
            }

            return @stream_isatty($stream);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
