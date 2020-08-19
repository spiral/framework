<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
     *
     * @param int $code
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct(?int $code = null, string $message = '', ?\Throwable $previous = null)
    {
        if (empty($code) && empty($this->code)) {
            $code = self::BAD_DATA;
        }

        if (empty($message)) {
            $message = "Http Error - {$code}";
        }

        parent::__construct($message, $code, $previous);
    }
}
