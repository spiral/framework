<?php

declare(strict_types=1);

namespace Spiral\Views;

interface GlobalVariablesInterface
{
    /**
     * Set a global variable.
     */
    public function set(string $name, mixed $data): void;

    /**
     * Get all global variables.
     */
    public function getAll(): array;
}
