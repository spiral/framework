<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Psr\Http\Message\ResponseInterface;

final class AuthorizationStatus
{
    public function __construct(
        public readonly bool $success,
        public readonly array $topics,
        public readonly array $attributes = [],
        public readonly ?ResponseInterface $response = null
    ) {
    }

    /**
     * Check if response is set.
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }
}
