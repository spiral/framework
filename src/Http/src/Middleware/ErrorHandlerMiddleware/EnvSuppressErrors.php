<?php

declare(strict_types=1);

namespace Spiral\Http\Middleware\ErrorHandlerMiddleware;

use Spiral\Boot\EnvironmentInterface;

class EnvSuppressErrors implements SuppressErrorsInterface
{
    public function __construct(
        private EnvironmentInterface $environment
    ) {
    }

    public function suppressed(): bool
    {
        return !$this->environment->get('DEBUG', false);
    }
}
