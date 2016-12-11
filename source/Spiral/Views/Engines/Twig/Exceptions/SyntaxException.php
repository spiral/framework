<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines\Twig\Exceptions;

use Spiral\Views\LoaderInterface;

/**
 * Provides ability to clarify syntax error location.
 */
class SyntaxException extends \RuntimeException
{
    /**
     * Clarify twig syntax exception.
     *
     * @param \Twig_Error_Syntax $error
     * @param LoaderInterface    $loader
     *
     * @return self
     */
    public static function fromTwig(
        \Twig_Error_Syntax $error,
        LoaderInterface $loader
    ): SyntaxException {
        $exception = new static($error->getMessage(), $error->getCode(), $error);
        $exception->file = $loader->localFilename($error->getSourceContext()->getPath());
        $exception->line = $error->getTemplateLine();

        return $exception;
    }
}