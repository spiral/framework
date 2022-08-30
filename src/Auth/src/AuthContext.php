<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth;

final class AuthContext implements AuthContextInterface
{
    /** @var ActorProviderInterface */
    private $actorProvider;

    /** @var TokenInterface|null */
    private $token;

    /** @var object|null */
    private $actor;

    /** @var string|null */
    private $transport;

    /** @var bool */
    private $closed = false;

    public function __construct(ActorProviderInterface $actorProvider)
    {
        $this->actorProvider = $actorProvider;
    }

    /**
     * @inheritDoc
     */
    public function start(TokenInterface $token, string $transport = null): void
    {
        $this->closed = false;
        $this->actor = null;
        $this->token = $token;
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function getTransport(): ?string
    {
        return $this->transport;
    }

    /**
     * @inheritDoc
     */
    public function getActor(): ?object
    {
        if ($this->closed) {
            return null;
        }

        if ($this->actor === null && $this->token !== null) {
            $this->actor = $this->actorProvider->getActor($this->token);
        }

        return $this->actor;
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->closed = true;
        $this->actor = null;
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }
}
