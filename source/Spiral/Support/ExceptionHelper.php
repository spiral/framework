<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Support;

use Spiral\Tokenizer\Highlighter;
use Spiral\Tokenizer\Highlighter\Style;

/**
 * Helper class for spiral exceptions.
 */
class ExceptionHelper
{
    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    public static function createMessage(\Throwable $exception)
    {
        return \Spiral\interpolate(
            '{exception}: {message} in {file} at line {line}',
            [
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine()
            ]
        );
    }

    /**
     * Highlight file source.
     *
     * @param string $filename
     * @param int    $line
     * @param int    $around
     * @param Style  $style
     *
     * @return string
     */
    public static function highlightSource(
        string $filename,
        int $line,
        int $around = 10,
        Style $style = null
    ) {
        $highlighter = new Highlighter(file_get_contents($filename), $style ?? new Style());

        return $highlighter->lines($line, $around);
    }
}