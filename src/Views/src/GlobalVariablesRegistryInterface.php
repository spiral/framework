<?php

declare(strict_types=1);

namespace Spiral\Views;

interface GlobalVariablesRegistryInterface
{
    /**
     * Registers a new global variable.
     */
    public function registerVariable(string $name, mixed $data): void;

    /**
     * Get all registered global variables.
     */
    public function getVariables(): array;
}
