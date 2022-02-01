<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Psr\Container\ContainerInterface;

/**
 * Provides the ability to associate custom handlers and serializes with the specific job name.
 *
 * @deprecated since 2.9. Will be removed since 3.0
 */
final class JobRegistry implements HandlerRegistryInterface, SerializerRegistryInterface
{
    /** @var array */
    private $handlers = [];

    /** @var array */
    private $serializers = [];

    /** @var array */
    private $pipelines = [];

    /** @var ContainerInterface */
    private $container;

    /** @var HandlerRegistryInterface */
    private $fallbackHandlers;

    /** @var SerializerRegistryInterface */
    private $fallbackSerializers;

    /**
     * @param ContainerInterface $container
     * @param HandlerRegistryInterface $handlers
     * @param SerializerRegistryInterface $serializers
     */
    public function __construct(
        ContainerInterface $container,
        HandlerRegistryInterface $handlers,
        SerializerRegistryInterface $serializers
    ) {
        $this->container = $container;
        $this->fallbackHandlers = $handlers;
        $this->fallbackSerializers = $serializers;
    }

    /**
     * @param string $jobType
     * @param HandlerInterface|string $handler
     */
    public function setHandler(string $jobType, $handler): void
    {
        $this->handlers[$jobType] = $handler;
    }

    /**
     * @param string $jobType
     * @return HandlerInterface
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
     * @param string $jobType
     * @param SerializerInterface|string $serializer
     */
    public function setSerializer(string $jobType, $serializer): void
    {
        $this->serializers[$jobType] = $serializer;
    }

    /**
     * @param string $jobType
     * @return SerializerInterface
     */
    public function getSerializer(string $jobType): SerializerInterface
    {
        if (isset($this->serializers[$jobType])) {
            if ($this->serializers[$jobType] instanceof SerializerInterface) {
                return $this->serializers[$jobType];
            }

            return $this->container->get($this->serializers[$jobType]);
        }

        if (!isset($this->handlers[$jobType])) {
            return $this->fallbackSerializers->getSerializer($jobType);
        }

        $handler = $this->getHandler($jobType);
        if ($handler instanceof SerializerInterface) {
            return $handler;
        }

        return $this->fallbackSerializers->getSerializer($jobType);
    }

    /**
     * @param string $jobType
     * @param string $pipeline
     */
    public function setPipeline(string $jobType, string $pipeline): void
    {
        $this->pipelines[$jobType] = $pipeline;
    }

    /**
     * @param string $jobType
     * @return string|null
     */
    public function getPipeline(string $jobType): ?string
    {
        return $this->pipelines[$jobType] ?? null;
    }
}
