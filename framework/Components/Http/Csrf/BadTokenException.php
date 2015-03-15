<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Csrf;

use Spiral\Components\Http\Response;
use Spiral\Core\Dispatcher\ClientException;

class BadTokenException extends ClientException
{
    /**
     * Due response code stored in exception message, errorCode() method can be used to retrieve
     * it via more recognizable way. ClientException was used originally for Console and Http
     * dispatchers, now it used only for Http.
     *
     * @return int
     */
    public function errorCode()
    {
        return Response::BAD_REQUEST;
    }
}