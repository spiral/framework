<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

final class CallContext implements CallContextInterface
{
    use AttributedTrait;

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function __construct(
        private TargetInterface $target,
        private array $arguments = [],
        array $attributes = [],
    ) {
        $this->attributes = $attributes;
    }

    public function getTarget(): TargetInterface
    {
        return $this->target;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function withTarget(TargetInterface $target): static
    {
        $clone = clone $this;
        $clone->target = $target;
        return $clone;
    }

    public function withArguments(array $arguments): static
    {
        $clone = clone $this;
        $clone->arguments = $arguments;
        return $clone;
    }
}
