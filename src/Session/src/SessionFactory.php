<?php

declare(strict_types=1);

namespace Spiral\Session;

use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\FactoryInterface;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\Exception\MultipleSessionException;
use Spiral\Session\Exception\SessionException;

/**
 * Initiates session instance and configures session handlers.
 */
#[Singleton]
#[Scope('http')]
final class SessionFactory implements SessionFactoryInterface
{
    public function __construct(
        private readonly SessionConfig $config,
        private readonly FactoryInterface $factory
    ) {
    }

    public function initSession(string $clientSignature, string $id = null): SessionInterface
    {
        if (\session_status() === PHP_SESSION_ACTIVE) {
            throw new MultipleSessionException('Unable to initiate session, session already started');
        }

        // Initiating proper session handler
        if ($this->config->getHandler() !== null) {
            try {
                $handler = $this->config->getHandler()->resolve($this->factory);
            } catch (\Throwable | ContainerExceptionInterface $e) {
                throw new SessionException($e->getMessage(), (int) $e->getCode(), $e);
            }

            \session_set_save_handler($handler, true);
        }

        return $this->factory->make(Session::class, [
            'clientSignature' => $clientSignature,
            'lifetime' => $this->config->getLifetime(),
            'id' => $id,
        ]);
    }
}
