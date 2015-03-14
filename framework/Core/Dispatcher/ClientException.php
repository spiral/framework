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
     * Default set of error codes.
     */
    const BAD_REQUEST  = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN    = 403;
    const NOT_FOUND    = 404;
    const SERVER_ERROR = 500;

    /**
     * Due response code stored in exception message, errorCode() method can be used to retrieve
     * it via more recognizable way. ClientException was used originally for Console and Http
     * dispatchers, now it used only for Http.
     *
     * @return int
     */
    public function errorCode()
    {
        if (!is_numeric($this->getMessage()))
        {
            return Response::BAD_REQUEST;
        }

        return $this->getMessage();
    }
}