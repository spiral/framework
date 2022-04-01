<?php

declare(strict_types=1);

namespace Spiral\Http\Exception\ClientException;

use Spiral\Http\Exception\ClientException;

/**
 * HTTP 400 exception.
 */
class BadRequestException extends ClientException
{
    /** @var int */
    protected $code = ClientException::BAD_DATA;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($this->code, $message, $previous);
    }
}
