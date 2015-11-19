<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Exceptions\Response;

use Spiral\Http\Exceptions\ClientException;

/**
 * HTTP 400 exception.
 */
class BadRequestException extends ClientException
{
    /**
     * @var int
     */
    protected $code = ClientException::BAD_DATA;

    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($this->code, $message);
    }
}