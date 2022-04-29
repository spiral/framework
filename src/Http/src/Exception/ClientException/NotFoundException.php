<?php

declare(strict_types=1);

namespace Spiral\Http\Exception\ClientException;

use Spiral\Http\Exception\ClientException;

/**
 * HTTP 404 exception.
 */
class NotFoundException extends ClientException
{
    /** @var int */
    protected $code = ClientException::NOT_FOUND;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($this->code, $message, $previous);
    }
}
