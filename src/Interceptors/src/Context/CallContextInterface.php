<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

interface CallContextInterface extends AttributedInterface
{
    public function getTarget(): TargetInterface;

    public function getArguments(): array;

    public function withTarget(TargetInterface $target): static;

    public function withArguments(array $arguments): static;
}
