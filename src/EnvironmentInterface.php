<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

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
    public function set(string $name, string $value);

    /**
     * Get environment value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, string $default = null): ?string;
}