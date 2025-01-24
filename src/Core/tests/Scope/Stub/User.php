<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

final class User implements UserInterface
{
    public function __construct(
        private string $name,
    ) {}

    public function setName(string|\Stringable $name): void
    {
        $this->name = (string) $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
