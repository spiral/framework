<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Auth\Event\Authenticated;
use Spiral\Auth\Event\Logout;

final class AuthContext implements AuthContextInterface
{
    private ?TokenInterface $token = null;
    private ?object $actor = null;
    private ?string $transport = null;
    private bool $closed = false;

    public function __construct(
        private readonly ActorProviderInterface $actorProvider,
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
    }

    public function start(TokenInterface $token, string $transport = null): void
    {
        $this->closed = false;
        $this->actor = null;
        $this->token = $token;
        $this->transport = $transport;

        $this->eventDispatcher?->dispatch(new Authenticated($token, $transport));
    }

    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    public function getTransport(): ?string
    {
        return $this->transport;
    }

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

    public function close(): void
    {
        // Store for Event Dispatcher
        $actor = $this->actor;

        $this->closed = true;
        $this->actor = null;

        /** The {@see Logout} event should be processed after state reset. */
        $this->eventDispatcher?->dispatch(new Logout($actor, $this->transport));
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }
}
