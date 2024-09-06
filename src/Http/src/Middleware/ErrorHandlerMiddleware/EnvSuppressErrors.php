<?php

declare(strict_types=1);

namespace Spiral\Http\Middleware\ErrorHandlerMiddleware;

use Spiral\Boot\Environment\DebugMode;

class EnvSuppressErrors implements SuppressErrorsInterface
{
    public function __construct(
        private readonly DebugMode $debugMode
    ) {
    }

    public function suppressed(): bool
    {
        return !$this->debugMode->isEnabled();
    }
}
