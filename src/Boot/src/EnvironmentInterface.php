<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Provides light abstraction at top of current environment values.
 */
interface EnvironmentInterface
{
    /**
     * Unique environment ID.
     */
    public function getID(): string;

    /**
     * Set environment value.
     */
    public function set(string $name, mixed $value): self;

    /**
     * Get environment value.
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Get all environment values.
     */
    public function getAll(): array;
}
