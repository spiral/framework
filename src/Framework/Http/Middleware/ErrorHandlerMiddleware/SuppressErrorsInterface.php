<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Spiral\Http\Middleware\ErrorHandlerMiddleware;

interface SuppressErrorsInterface
{
    /**
     * Should errors be suppressed?
     * @return bool
     */
    public function suppressed(): bool;
}
