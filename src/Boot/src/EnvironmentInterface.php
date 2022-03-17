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
    public function set(string $name, mixed $value): void;

    /**
     * Get environment value.
     *
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Get all environment values.
     */
    public function getAll(): array;
}
