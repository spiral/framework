<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\SerializerRegistryInterface as QueueSerializerRegistryInterface;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistryInterface;

final class QueueRegistry implements HandlerRegistryInterface, QueueSerializerRegistryInterface
{
    /** @var array<string, class-string> */
    private array $handlers = [];

    /** @var array<string, SerializerInterface> */
    private array $serializers = [];

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

    /**
     * Associate specific job type with serializer class or object
     */
    public function setSerializer(string $jobType, SerializerInterface|string|Autowire $serializer): void
    {
        /** @var SerializerRegistryInterface $registry */
        $registry = $this->container->get(SerializerRegistryInterface::class);

        if ($serializer instanceof Autowire) {
            $serializer = $this->container->get($serializer);
        }

        if (\is_string($serializer)) {
            $serializer = $registry->has($serializer) ?
                $registry->get($serializer) :
                $this->container->get($serializer);
        }

        if (!$serializer instanceof SerializerInterface) {
            throw new InvalidArgumentException(\sprintf(
                'Serializer must be an instance of `SerializerInterface` but `%s` given.',
                \get_debug_type($serializer)
            ));
        }

        $this->serializers[$jobType] = $serializer;
    }

    /**
     * Get serializer object for given job type
     */
    public function getSerializer(?string $jobType = null): SerializerInterface
    {
        if ($jobType && $this->hasSerializer($jobType)) {
            return $this->serializers[$jobType];
        }

        return $this->container->get(SerializerManager::class)->getSerializer();
    }

    public function hasSerializer(string $jobType): bool
    {
        return isset($this->serializers[$jobType]);
    }
}
