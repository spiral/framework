<?php

declare(strict_types=1);

namespace Spiral\Session\Exception;

use Spiral\Session\Middleware\SessionMiddleware;

final class InvalidSessionContext extends SessionException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? \sprintf(
            'The `%s` attribute was not found. To use the session, the `%s` must be configured.',
            SessionMiddleware::ATTRIBUTE,
            SessionMiddleware::class
        ));
    }
}
