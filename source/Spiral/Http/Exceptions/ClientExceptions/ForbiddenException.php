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
 * HTTP 403 exception.
 */
class ForbiddenException extends ClientException
{
    /**
     * @var int
     */
    protected $code = Response::FORBIDDEN;

    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($this->code, $message);
    }
}