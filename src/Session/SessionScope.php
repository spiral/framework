<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Session;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\ScopeException;

/**
 * Provides access to the currently active session scope.
 */
final class SessionScope implements SessionInterface, SingletonInterface
{
    /** Locations for unnamed segments i.e. default segment. */
    private const DEFAULT_SECTION = '_DEFAULT';

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function isStarted(): bool
    {
        return $this->getActiveSession()->isStarted();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function resume()
    {
        return $this->getActiveSession()->resume();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getID(): ?string
    {
        return $this->getActiveSession()->getID();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function regenerateID(): SessionInterface
    {
        $this->getActiveSession()->regenerateID();

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function commit(): bool
    {
        return $this->getActiveSession()->commit();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function abort(): bool
    {
        return $this->getActiveSession()->abort();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function destroy(): bool
    {
        return $this->getActiveSession()->destroy();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getSection(string $name = null): SessionSectionInterface
    {
        return new SectionScope($this, $name ?? self::DEFAULT_SECTION);
    }

    /**
     * @return SessionInterface
     *
     * @throws ScopeException
     */
    public function getActiveSession(): SessionInterface
    {
        try {
            return $this->container->get(SessionInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to receive active session, invalid request scope', $e->getCode(), $e);
        }
    }
}