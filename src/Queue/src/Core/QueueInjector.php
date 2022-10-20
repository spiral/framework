<?php

declare(strict_types=1);

namespace Spiral\Queue\Core;

use ReflectionClass;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueInterface;

/**
 * @implements InjectorInterface<QueueInterface>
 */
final class QueueInjector implements InjectorInterface
{
    public function __construct(
        private readonly QueueConnectionProviderInterface $queueManager
    ) {
    }

    public function createInjection(ReflectionClass $class, string $context = null): QueueInterface
    {
        try {
            if ($context === null) {
                $connection = $this->queueManager->getConnection();
            } else {
                // Get Queue by context
                try {
                    $connection = $this->queueManager->getConnection($context);
                } catch (InvalidArgumentException) {
                    // Case when context doesn't match to configured connections
                    return $this->queueManager->getConnection();
                }
            }
        } catch (\Throwable $e) {
            throw new ContainerException(\sprintf("Can't inject the required queue. %s", $e->getMessage()), 0, $e);
        }

        return $connection;
    }
}
