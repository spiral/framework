<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Psr\Http\Message\ResponseInterface;

interface ErrorsRendererInterface
{
    /**
     * Convert errors into a response object.
     * @param array<string, string> $errors
     */
    public function render(array $errors, mixed $context = null): ResponseInterface;
}
