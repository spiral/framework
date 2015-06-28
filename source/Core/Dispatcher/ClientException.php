<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Dispatcher;

use Spiral\Components\Http\Response;
use Spiral\Core\CoreException;

class ClientException extends CoreException
{
    /**
     * ClientException exceptions are not handled by Debugger and not logged into error log, this
     * exception classes used to define exceptions has to be handled by dispatcher itself.
     *
     * ClientException can be raised outside Dispatchers by Controllers and Core when method or
     * requested controller not found.
     */
    const BAD_DATA  = 400;
    const NOT_FOUND = 404;
    const ERROR     = 500;

    /**
     * Create ClientException with specified error code and optional message (parameters reverted).
     *
     * @param int|string $code
     * @param string     $message
     */
    public function __construct($code = Response::NOT_FOUND, $message = "")
    {
        parent::__construct($message, $code);
    }
}