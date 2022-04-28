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

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
