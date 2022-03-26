<?php

declare(strict_types=1);

namespace Spiral\Auth;

/**
 * Carries information about current actor, token and transport method.
 */
interface AuthContextInterface
{
    /**
     * Start new auth context based on a given token. Actor can be received on demand.
     */
    public function start(TokenInterface $token, string $transport = null): void;

    /**
     * Returns associated token if any.
     */
    public function getToken(): ?TokenInterface;

    /**
     * Get's transport which used to provide the token or transport which must store token value
     * in outgoing response.
     */
    public function getTransport(): ?string;

    /**
     * Returns actor associated with given token, resolves entity on demand. Use this method to check
     * if actor has been authenticated.
     */
    public function getActor(): ?object;

    /**
     * Closes context and declares token as invalid.
     */
    public function close(): void;

    /**
     * Indicates that context was closed and token must be removed by it's transport.
     */
    public function isClosed(): bool;
}
