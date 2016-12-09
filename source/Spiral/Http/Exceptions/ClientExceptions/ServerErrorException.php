<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Exceptions\ClientExceptions;

use Spiral\Http\Exceptions\ClientException;

/**
 * HTTP 500 exception.
 */
class ServerErrorException extends ClientException
{
    /**
     * @var int
     */
    protected $code = ClientException::ERROR;

    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($this->code, $message);
    }
}