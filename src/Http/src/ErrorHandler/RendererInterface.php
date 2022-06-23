<?php

declare(strict_types=1);

namespace Spiral\Http\ErrorHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Render exception content into response.
 */
interface RendererInterface
{
    public function renderException(Request $request, int $code, \Throwable $exception): Response;
}
