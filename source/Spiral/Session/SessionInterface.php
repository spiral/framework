<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

/**
 * API wrapping for native php sessions. Must provide ability for segment injections.
 */
interface SessionInterface //extends InjectorInterface
{
    /**
     * @return bool
     */
    public function isActive(): bool;

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
     * Must return true if session was destroyed.
     *
     * @return bool
     */
    public function destroy(): bool;

//    /**
//     * @param string|null $name
//     *
//     * @return \Spiral\Session\SectionInterface
//     */
//    public function getSegment(string $name = null): SectionInterface;
}