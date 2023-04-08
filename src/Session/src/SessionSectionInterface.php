<?php

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Session\Exception\SessionException;

/**
 * Singular session section (session data isolator).
 *
 * @extends \IteratorAggregate<array-key, mixed>
 * @extends \ArrayAccess<mixed, mixed>
 */
interface SessionSectionInterface extends \IteratorAggregate, \ArrayAccess
{
    /**
     * Section name.
     */
    public function getName(): string;

    /**
     * All section data in a form of array.
     */
    public function getAll(): array;

    /**
     * Set data in session.
     *
     * @throws SessionException
     */
    public function set(string $name, mixed $value): self;

    /**
     * Check if value presented in session.
     *
     * @throws SessionException
     */
    public function has(string $name): bool;

    /**
     * Get value stored in session.
     *
     * @throws SessionException
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Read item from session and delete it after.
     *
     * @param mixed $default Default value when no such item exists.
     * @throws SessionException
     */
    public function pull(string $name, mixed $default = null): mixed;

    /**
     * Delete data from session.
     *
     * @throws SessionException
     */
    public function delete(string $name): void;

    /**
     * Clear all session section data.
     */
    public function clear(): void;
}
