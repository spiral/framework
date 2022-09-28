<?php

declare(strict_types=1);

namespace Spiral\Filter;

use Psr\Http\Message\ResponseInterface;
use Spiral\Filters\ErrorsRendererInterface;
use Spiral\Http\ResponseWrapper;

final class JsonErrorsRenderer implements ErrorsRendererInterface
{
    public function __construct(
        private readonly ResponseWrapper $wrapper
    ) {
    }

    public function render(array $errors, mixed $context = null): ResponseInterface
    {
        return $this->wrapper->json([
            'errors' => $errors,
        ])
            ->withStatus(422, 'The given data was invalid.');
    }
}
