<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Psr\Http\Message\ResponseInterface;

final class AuthorizationStatus
{
    private bool $success;
    private array $topics;
    private array $attributes;
    private ?ResponseInterface $response;

    public function __construct(
        bool $success,
        array $topics,
        array $attributes = [],
        ?ResponseInterface $response = null
    ) {
        $this->success = $success;
        $this->topics = $topics;
        $this->attributes = $attributes;
        $this->response = $response;
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
