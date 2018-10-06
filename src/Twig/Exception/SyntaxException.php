<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig\Exception;

use Spiral\Views\Exception\CompileException;
use Twig\Error\SyntaxError;

class SyntaxException extends CompileException
{
    /**
     * @param SyntaxError $error
     * @return SyntaxException
     */
    public static function fromTwig(SyntaxError $error): SyntaxException
    {
        $exception = new static($error->getMessage(), $error->getCode(), $error);
        $exception->file = $error->getSourceContext()->getPath();
        $exception->line = $error->getTemplateLine();

        return $exception;
    }
}