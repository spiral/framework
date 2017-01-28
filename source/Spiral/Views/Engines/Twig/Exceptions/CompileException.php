<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Twig\Exceptions;

/**
 * Provides ability to clarify syntax error location.
 */
class CompileException extends \Spiral\Views\Exceptions\CompileException
{
    /**
     * Clarify twig syntax exception.
     *
     * @param \Twig_Error_Syntax $error
     *
     * @return self
     */
    public static function fromTwig(\Twig_Error_Syntax $error): CompileException
    {
        $exception = new static($error->getMessage(), $error->getCode(), $error);
        $exception->file = $error->getSourceContext()->getPath();
        $exception->line = $error->getTemplateLine();

        return $exception;
    }
}