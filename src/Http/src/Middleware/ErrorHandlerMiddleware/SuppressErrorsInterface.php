<?php

declare(strict_types=1);

namespace Spiral\Http\Middleware\ErrorHandlerMiddleware;

interface SuppressErrorsInterface
{
    /**
     * Should errors be suppressed?
     */
    public function suppressed(): bool;
}
