<?php

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Session\Exception\SessionException;

/**
 * API wrapping for native php sessions. Must provide ability for section injections.
 */
interface SessionInterface
{
    public function isStarted(): bool;

    /**
     * Resume session or start new one.
     *
     * @throws SessionException
     */
    public function resume(): void;

    /**
     * Current session ID. Null when session is destroyed.
     */
    public function getID(): ?string;

    /**
     * Regenerate session id without altering it's data.
     */
    public function regenerateID(): self;

    /**
     * Commit session data, must return true if data successfully saved.
     */
    public function commit(): bool;

    /**
     * Discard all the session changes and close the session.
     */
    public function abort(): bool;

    /**
     * Destroys all data associated with session but does not regenerate it IDs.
     */
    public function destroy(): bool;

    /**
     * @param string|null $name When null default section to be returned.
     */
    public function getSection(string $name = null): SessionSectionInterface;
}
