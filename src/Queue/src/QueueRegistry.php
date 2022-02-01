<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;

final class QueueRegistry implements HandlerRegistryInterface
{
    /** @var array<string, class-string>  */
    private $handlers = [];
    /** @var ContainerInterface */
    private $container;
    /** @var HandlerRegistryInterface  */
    private $fallbackHandlers;

    public function __construct(
        ContainerInterface $container,
        HandlerRegistryInterface $handlers
    ) {
        $this->container = $container;
        $this->fallbackHandlers = $handlers;
    }

    /**
     * Associate specific job type with handler class or object
     * @param HandlerInterface|string $handler
     */
    public function setHandler(string $jobType, $handler): void
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
