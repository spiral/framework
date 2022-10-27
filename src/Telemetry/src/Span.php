<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Ramsey\Uuid\Uuid;
use Spiral\Telemetry\Span\Status;

/**
 * @internal
 */
final class Span implements SpanInterface
{
    private ?Status $status = null;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private string $name,
        private array $attributes = []
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function updateName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function setStatus(string|int $code, string $description = null): self
    {
        $this->status = new Status($code, $description);

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }
}
