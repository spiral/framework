<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Psr\Http\Message\ResponseInterface;

final class AuthorizationStatus
{
    public function __construct(
        private readonly bool $success,
        private readonly array $topics,
        private readonly array $attributes = [],
        private readonly ?ResponseInterface $response = null
    ) {
    }

    /**
     * Check if authorization status is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Get list of authorized topics.
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Check if response is set.
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * Get response object.
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
