<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Exceptions\ClientExceptions;

use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Response;

/**
 * HTTP 500 exception.
 */
class ServerErrorException extends ClientException
{
    /**
     * @var int
     */
    protected $code = Response::SERVER_ERROR;

    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($this->code, $message);
    }
}