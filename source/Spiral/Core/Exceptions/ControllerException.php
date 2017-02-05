<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Exceptions;

/**
 * Unable to perform user action or find controller.
 */
class ControllerException extends RuntimeException
{
    /**
     * Pre-defined controller error codes.
     */
    const NOT_FOUND    = 0;
    const BAD_ACTION   = 1;
    const BAD_ARGUMENT = 2;
    const FORBIDDEN    = 3;
    const ERROR        = 4;
}