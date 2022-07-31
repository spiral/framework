<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Serializer\SerializerRegistryInterface;

final class QueueRegistry implements HandlerRegistryInterface
{
    /** @var array<string, class-string>  */
    private array $handlers = [];

    /** @var array<string, non-empty-string>  */
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
     * Associate specific job type with serializer format
     */
    public function setSerializerFormat(string $jobType, string $format): void
    {
        if (!$this->container->get(SerializerRegistryInterface::class)->has($format)) {
            throw new InvalidArgumentException(\sprintf('Serializer format `%s` not found.', $format));
        }

        $this->serializers[$jobType] = $format;
    }

    public function getSerializerFormat(string $jobType): string
    {
        if ($this->hasSerializer($jobType)) {
            return $this->serializers[$jobType];
        }

        return throw new InvalidArgumentException(
            \sprintf('Serializer format associated with job type `%s` not found.', $jobType)
        );
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

    public function hasSerializer(string $jobType): bool
    {
        return isset($this->serializers[$jobType]);
    }
}
