<?php

declare(strict_types=1);

namespace Spiral\Views;

final class GlobalVariables implements GlobalVariablesInterface
{
    public function __construct(
        private array $variables = []
    ) {
    }

    public function set(string $name, mixed $data): void
    {
        $this->variables[$name] = $data;
    }

    public function getAll(): array
    {
        return $this->variables;
    }
}
