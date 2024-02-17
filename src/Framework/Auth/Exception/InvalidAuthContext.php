<?php

declare(strict_types=1);

namespace Spiral\Auth\Exception;

use Spiral\Auth\Middleware\AuthMiddleware;

final class InvalidAuthContext extends AuthException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? \sprintf(
            'The `%s` attribute was not found. To use the auth, the `%s` must be configured.',
            AuthMiddleware::ATTRIBUTE,
            AuthMiddleware::class
        ));
    }
}
