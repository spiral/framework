<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;

final class QueueRegistry implements HandlerRegistryInterface
{
    /** @var array<string, class-string>  */
    private array $handlers = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly HandlerRegistryInterface $fallbackHandlers
    ) {
    }

    /**
     * Associate specific job type with handler class or object
     */
    public function setHandler(string $jobType, HandlerInterface|string $handler): void
    {
        $this->handlers[$jobType] = $handler;
    }

    /**
     * Get handler object for given job type
     */
    public function getHandler(string $jobType): HandlerInterface
    {
        if (isset($this->handlers[$jobType])) {
            if ($this->handlers[$jobType] instanceof HandlerInterface) {
                return $this->handlers[$jobType];
            }

            return $this->container->get($this->handlers[$jobType]);
        }

        return $this->fallbackHandlers->getHandler($jobType);
    }
}
