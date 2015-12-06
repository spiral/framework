<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Support;

use Spiral\Tokenizer\Highlighter;
use Spiral\Tokenizer\Highlighter\InversedStyle;
use Spiral\Tokenizer\Highlighter\Style;
use Spiral\Tokenizer\TokenizerInterface;

/**
 * Helper class for spiral exceptions.
 *
 * needs shared container and tokenizer binding
 */
class ExceptionSupport
{
    /**
     * @param \Throwable $exception
     * @return string
     */
    public static function createMessage($exception)
    {
        return interpolate('{exception}: {message} in {file} at line {line}', [
            'exception' => get_class($exception),
            'message'   => $exception->getMessage(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine()
        ]);
    }

    /**
     * Highlight file source.
     *
     * @param string $filename
     * @param int    $line
     * @param int    $around
     * @param Style  $style
     * @return string
     */
    public static function highlightSource($filename, $line, $around = 10, Style $style = null)
    {
        $highlighter = new Highlighter(
            $filename,
            !empty($style) ? $style : new Style(),
            spiral(TokenizerInterface::class)
        );

        return $highlighter->lines($line, $around);
    }
}