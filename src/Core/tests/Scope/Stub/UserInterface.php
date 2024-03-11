<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

interface UserInterface
{
    public function setName(string $name): void;

    public function getName(): string;
}
