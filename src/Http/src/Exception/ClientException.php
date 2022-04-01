<?php

declare(strict_types=1);

namespace Spiral\Http\Exception;

/**
 * Generic client driven http exception.
 */
class ClientException extends HttpException
{
    /**
     * Most common codes.
     */
    public const BAD_DATA = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const ERROR = 500;

    /**
     * Code and message positions are reverted.
     */
    public function __construct(?int $code = null, string $message = '', ?\Throwable $previous = null)
    {
        if (empty($code) && empty($this->code)) {
            $code = self::BAD_DATA;
        }

        if (empty($message)) {
            $message = \sprintf('Http Error - %s', $code);
        }

        parent::__construct($message, $code, $previous);
    }
}
