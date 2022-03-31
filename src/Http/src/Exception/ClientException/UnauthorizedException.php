<?php

declare(strict_types=1);

namespace Spiral\Http\Exception\ClientException;

use Spiral\Http\Exception\ClientException;

/**
 * HTTP 401 exception.
 */
class UnauthorizedException extends ClientException
{
    /** @var int */
    protected $code = ClientException::UNAUTHORIZED;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($this->code, $message, $previous);
    }
}
