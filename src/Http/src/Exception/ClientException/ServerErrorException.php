<?php

declare(strict_types=1);

namespace Spiral\Http\Exception\ClientException;

use Spiral\Http\Exception\ClientException;

/**
 * HTTP 500 exception.
 */
class ServerErrorException extends ClientException
{
    /** @var int */
    protected $code = ClientException::ERROR;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($this->code, $message, $previous);
    }
}
