<?php

declare(strict_types=1);

namespace Spiral\Http\Exception\ClientException;

use Spiral\Http\Exception\ClientException;

/**
 * HTTP 403 exception.
 */
class ForbiddenException extends ClientException
{
    /** @var int */
    protected $code = ClientException::FORBIDDEN;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($this->code, $message, $previous);
    }
}
