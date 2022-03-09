<?php

declare(strict_types=1);

namespace Spiral\Queue\Core;

use ReflectionClass;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueInterface;

/**
 * @implements InjectorInterface<QueueInterface>
 */
class QueueInjector implements InjectorInterface
{
    private QueueConnectionProviderInterface $queueManager;

    public function __construct(QueueConnectionProviderInterface $queueManager)
    {
        $this->queueManager = $queueManager;
    }

    public function createInjection(ReflectionClass $class, string $context = null): QueueInterface
    {
        if ($context === null) {
            $connection = $this->queueManager->getConnection();
        } else {
            // Get Queue by context
            try {
                $connection = $this->queueManager->getConnection($context);
            } catch (InvalidArgumentException $e) {
                // Case when context doesn't match to configured connections
                return $this->queueManager->getConnection();
            }
        }

        // User specified a specific class type
        $className = $class->getName();
        if ($className !== QueueInterface::class && !$connection instanceof $className) {
            throw new \RuntimeException(
                \sprintf(
                    "The queue obtained by the context `%s` doesn't match the type `%s`.",
                    $context,
                    $className
                )
            );
        }

        return $connection;
    }
}
