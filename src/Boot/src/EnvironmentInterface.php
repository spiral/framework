<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Provides light abstraction at top of current environment values.
 */
interface EnvironmentInterface
{
    /**
     * Unique environment ID.
     *
     * @return string
     */
    public function getID(): string;

    /**
     * Set environment value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, $value);

    /**
     * Get environment value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * Get all environment values.
     *
     * @return array
     */
    public function getAll(): array;
}
