<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Logger\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Core\ContainerScope;
use Spiral\Logger\LogsInterface;

/**
 * Logger trait provides access to the logger from the global container scope (if exists).
 */
trait LoggerTrait
{
    /** @var LoggerInterface|null @internal */
    private $logger = null;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Get associated or create new instance of LoggerInterface.
     *
     * @param string $channel
     * @return LoggerInterface
     */
    protected function getLogger(string $channel = null): LoggerInterface
    {
        if ($channel !== null) {
            return $this->allocateLogger($channel);
        }

        if ($this->logger !== null) {
            return $this->logger;
        }

        //We are using class name as log channel (name) by default
        return $this->logger = $this->allocateLogger(static::class);
    }

    /**
     * Create new instance of associated logger (on demand creation).
     *
     * @param string $channel
     * @return LoggerInterface
     */
    private function allocateLogger(string $channel): LoggerInterface
    {
        $container = ContainerScope::getContainer();
        if (empty($container) || !$container->has(LogsInterface::class)) {
            return $this->logger ?? new NullLogger();
        }

        //We are using class name as log channel (name) by default
        return $container->get(LogsInterface::class)->getLogger($channel);
    }
}
