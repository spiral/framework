<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Serializer\SerializerInterface;
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
     * Associate specific job type with serializer
     */
    public function setSerializer(string $jobType, string|Autowire|SerializerInterface $serializer): void
    {
        /** @var SerializerRegistryInterface $registry */
        $registry = $this->container->get(SerializerRegistryInterface::class);

        if ($serializer instanceof Autowire) {
            $serializer = $this->container->get($serializer);
        }

        $name = $serializer;
        if ($serializer instanceof SerializerInterface) {
            $name = $serializer::class;
        }

        if (!$registry->has($name) && !$registry->hasByClass($name)) {
            $name = $this->registerSerializer($serializer);
        }

        $this->serializers[$jobType] = $registry->has($name) ? $name : $registry->getNameByClass($name);
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

    private function registerSerializer(SerializerInterface|string $serializer): string
    {
        /** @var SerializerRegistryInterface $registry */
        $registry = $this->container->get(SerializerRegistryInterface::class);

        if ($serializer instanceof SerializerInterface) {
            $registry->register($serializer::class, $serializer);

            return $serializer::class;
        }

        if (\class_exists($serializer)) {
            $registry->register($serializer, $this->container->get($serializer));

            return $serializer;
        }

        throw new InvalidArgumentException(\sprintf('Serializer with name or class `%s` not found.', $serializer));
    }
}
