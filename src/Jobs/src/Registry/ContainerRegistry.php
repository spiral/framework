<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs\Registry;

use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Jobs\Exception\JobException;
use Spiral\Jobs\HandlerInterface;
use Spiral\Jobs\HandlerRegistryInterface;
use Spiral\Jobs\JsonSerializer;
use Spiral\Jobs\SerializerInterface;
use Spiral\Jobs\SerializerRegistryInterface;

/**
 * Resolve handler from container binding.
 */
final class ContainerRegistry implements HandlerRegistryInterface, SerializerRegistryInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var SerializerInterface */
    private $defaultSerializer;

    /** @var \Doctrine\Inflector\Inflector */
    private $inflector;

    /**
     * @param ContainerInterface       $container
     * @param SerializerInterface|null $defaultSerializer
     */
    public function __construct(ContainerInterface $container, SerializerInterface $defaultSerializer = null)
    {
        $this->container = $container;
        $this->defaultSerializer = $defaultSerializer ?? new JsonSerializer();
        $this->inflector = (new \Doctrine\Inflector\Rules\English\InflectorFactory())->build();
    }

    /**
     * @inheritdoc
     */
    public function getHandler(string $jobType): HandlerInterface
    {
        try {
            $handler = $this->container->get($this->className($jobType));
        } catch (ContainerException $e) {
            throw new JobException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$handler instanceof HandlerInterface) {
            throw new JobException("Unable to resolve job handler for `{$jobType}`");
        }

        return $handler;
    }

    /**
     * @param string $jobType
     * @return SerializerInterface
     */
    public function getSerializer(string $jobType): SerializerInterface
    {
        try {
            $handler = $this->getHandler($jobType);
        } catch (JobException $e) {
            return $this->defaultSerializer;
        }

        if ($handler instanceof SerializerInterface) {
            return $handler;
        }

        return $this->defaultSerializer;
    }

    /**
     * @param string $jobType
     * @return string
     */
    private function className(string $jobType): string
    {
        $names = explode('.', $jobType);
        $names = array_map(function (string $value) {
            return $this->inflector->classify($value);
        }, $names);

        return join('\\', $names);
    }
}
