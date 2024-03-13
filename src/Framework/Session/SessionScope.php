<?php

declare(strict_types=1);

namespace Spiral\Session;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Exception\ScopeException;

/**
 * Provides access to the currently active session scope.
 * @deprecated Use {@see SessionInterface} instead. Will be removed in v4.0.
 */
#[Singleton]
final class SessionScope implements SessionInterface
{
    /** Locations for unnamed segments i.e. default segment. */
    private const DEFAULT_SECTION = '_DEFAULT';

    public function __construct(
        #[Proxy] private readonly ContainerInterface $container
    ) {
    }

    /**
     * @throws ScopeException
     */
    public function isStarted(): bool
    {
        return $this->getActiveSession()->isStarted();
    }

    /**
     * @throws ScopeException
     */
    public function resume(): void
    {
        $this->getActiveSession()->resume();
    }

    /**
     * @throws ScopeException
     */
    public function getID(): ?string
    {
        return $this->getActiveSession()->getID();
    }

    /**
     * @throws ScopeException
     */
    public function regenerateID(): SessionInterface
    {
        $this->getActiveSession()->regenerateID();

        return $this;
    }

    /**
     * @throws ScopeException
     */
    public function commit(): bool
    {
        return $this->getActiveSession()->commit();
    }

    /**
     * @throws ScopeException
     */
    public function abort(): bool
    {
        return $this->getActiveSession()->abort();
    }

    /**
     * @throws ScopeException
     */
    public function destroy(): bool
    {
        return $this->getActiveSession()->destroy();
    }

    /**
     * @throws ScopeException
     */
    public function getSection(string $name = null): SessionSectionInterface
    {
        return new SectionScope($this, $name ?? self::DEFAULT_SECTION);
    }

    /**
     * @throws ScopeException
     */
    public function getActiveSession(): SessionInterface
    {
        try {
            return $this->container->get(SessionInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException(
                'Unable to receive active session, invalid request scope',
                $e->getCode(),
                $e
            );
        }
    }
}
