<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

use Spiral\Core\Container\InjectorInterface;

/**
 * API wrapping for native php sessions. Must provide ability for section injections.
 */
interface SessionInterface extends InjectorInterface
{
    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Resume session or start new one.
     *
     * @throws \Spiral\Session\Exceptions\SessionException
     */
    public function resume();

    /**
     * Current session ID. Null when session is destroyed.
     *
     * @return null|string
     */
    public function getID();

    /**
     * Regenerate session id.
     *
     * @param bool $destruct Set to true to remove old session data.
     *
     * @return self
     */
    public function regenerateID(bool $destruct = false): self;

    /**
     * Commit session data, must return true if data successfully saved.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Destroys session and all associated data within it.
     *
     * @return bool
     */
    public function destroy(): bool;

    /**
     * @param string|null $name When null default section to be returned.
     *
     * @return SessionSectionInterface
     */
    public function getSection(string $name = null): SessionSectionInterface;
}