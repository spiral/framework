<?php

declare(strict_types=1);

namespace Spiral\Core\Context;

use Spiral\Core\Context\Attributed\AttributedTrait;
use Spiral\Core\Context\Target\TargetInterface;

final class CallContext implements CallContextInterface
{
    use AttributedTrait;

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
