<?php

declare(strict_types=1);

namespace Spiral\Core\Context;

interface CallContextInterface extends AttributedInterface
{
    public function getTarget(): mixed;

    public function getArguments(): array;

    public function withTarget(TargetInterface $target): static;

    public function withArguments(array $arguments): static;
}
