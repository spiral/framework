<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Psr\Http\Message\ResponseInterface;

final class AuthorizationStatus
{
    /**
     * @param non-empty-string[]|null $topics
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?array $topics,
        public readonly array $attributes = [],
        public readonly ?ResponseInterface $response = null
    ) {
    }

    /**
     * @param bool $success
     * @param non-empty-string[]|null $topics
     * @param array $attributes
     * @param ResponseInterface|null $response
     */
    public function with(mixed ...$values): self
    {
        return new self(...($values + (array)$this));
    }

    /**
     * Check if response is set.
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }
}
