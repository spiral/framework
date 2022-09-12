<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Config\QueueConfig;
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
        private readonly FactoryInterface $factory,
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
     * @throws \Throwable
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
     *
     * @psalm-param SerializerInterface|class-string|Autowire $serializer
     *
     * @throws InvalidArgumentException
     */
    public function setSerializer(string $jobType, SerializerInterface|string|Autowire $serializer): void
    {
        $this->serializers[$jobType] = $this->resolveSerializer($serializer);
    }

    /**
     * Get serializer object for given job type
     */
    public function getSerializer(?string $jobType = null): SerializerInterface
    {
        if ($jobType && $this->hasSerializer($jobType)) {
            return $this->serializers[$jobType];
        }

        $config = $this->container->get(QueueConfig::class);

        return $config->getDefaultSerializer() === null ?
            $this->container->get(SerializerManager::class)->getSerializer() :
            $this->resolveSerializer($config->getDefaultSerializer());
    }

    public function hasSerializer(string $jobType): bool
    {
        return isset($this->serializers[$jobType]);
    }

    /**
     * @psalm-param SerializerInterface|class-string<SerializerInterface>|Autowire<SerializerInterface> $serializer
     *
     * @throws InvalidArgumentException
     */
    private function resolveSerializer(SerializerInterface|string|Autowire $serializer): SerializerInterface
    {
        if ($serializer instanceof Autowire) {
            $serializer = $serializer->resolve($this->factory);
        }

        if (\is_string($serializer)) {
            $registry = $this->container->get(SerializerRegistryInterface::class);
            \assert($registry instanceof SerializerRegistryInterface);

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

        return $serializer;
    }
}
