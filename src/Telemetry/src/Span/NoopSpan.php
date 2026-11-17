<?php

declare(strict_types=1);

namespace Spiral\Telemetry\Span;

use Spiral\Telemetry\SpanInterface;

/**
 * The span {@see \Spiral\Telemetry\CurrentSpan} gives outside a trace: it keeps nothing, so code that may
 * run with or without a trace sets attributes unconditionally.
 */
final class NoopSpan implements SpanInterface
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function getName(): string
    {
        return 'noop';
    }

    public function updateName(string $name): self
    {
        return $this;
    }

    public function setStatus(string|int $code, ?string $description = null): self
    {
        return $this;
    }

    public function getStatus(): ?Status
    {
        return null;
    }

    public function getAttributes(): array
    {
        return [];
    }

    public function setAttributes(array $attributes): self
    {
        return $this;
    }

    public function setAttribute(string $name, mixed $value): self
    {
        return $this;
    }

    public function hasAttribute(string $name): bool
    {
        return false;
    }

    public function getAttribute(string $name): mixed
    {
        return null;
    }
}
