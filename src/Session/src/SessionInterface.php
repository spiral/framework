<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Session\Exception\SessionException;

/**
 * API wrapping for native php sessions. Must provide ability for section injections.
 */
interface SessionInterface
{
    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Resume session or start new one.
     *
     * @throws SessionException
     */
    public function resume();

    /**
     * Current session ID. Null when session is destroyed.
     *
     * @return null|string
     */
    public function getID(): ?string;

    /**
     * Regenerate session id without altering it's data.
     *
     * @return self
     */
    public function regenerateID(): self;

    /**
     * Commit session data, must return true if data successfully saved.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Discard all the session changes and close the session.
     *
     * @return bool
     */
    public function abort(): bool;

    /**
     * Destroys all data associated with session but does not regenerate it IDs.
     *
     * @return bool
     */
    public function destroy(): bool;

    /**
     * @param string|null $name When null default section to be returned.
     * @return SessionSectionInterface
     */
    public function getSection(string $name = null): SessionSectionInterface;
}
