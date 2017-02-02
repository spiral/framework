<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Session;

use Spiral\Session\Exceptions\SessionException;

/**
 * Singular session segment (session data isolator).
 */
interface SectionInterface extends \IteratorAggregate, \ArrayAccess
{
    /**
     * Set data in session.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     * @throws SessionException
     */
    public function set(string $name, $value);

    /**
     * Check if value presented in session.
     *
     * @param string $name
     *
     * @return bool
     * @throws SessionException
     */
    public function has(string $name);

    /**
     * Get value stored in session.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     * @throws SessionException
     */
    public function get(string $name, $default = null);

    /**
     * Read item from session and delete it after.
     *
     * @param string $name
     * @param mixed  $default Default value when no such item exists.
     *
     * @return mixed
     * @throws SessionException
     */
    public function pull(string $name, $default = null);

    /**
     * Delete data from session.
     *
     * @param string $name
     *
     * @throws SessionException
     */
    public function delete(string $name);
}
