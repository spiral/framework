<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

/**
 * Runs all the jobs in the same process.
 */
final class ShortCircuit implements QueueInterface
{
    /** @var int */
    private $id = 0;

    /** @var HandlerRegistryInterface */
    private $registry;

    /** @var SerializerRegistryInterface */
    private $serializerRegistry;

    /**
     * @param HandlerRegistryInterface $registry
     */
    public function __construct(HandlerRegistryInterface $registry, SerializerRegistryInterface $serializerRegistry)
    {
        $this->registry = $registry;
        $this->serializerRegistry = $serializerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function push(string $jobType, array $payload = [], Options $options = null): string
    {
        $payloadBody = $this->serialize($jobType, $payload);
        if ($options !== null && $options->getDelay()) {
            sleep($options->getDelay());
        }

        $id = (string)(++$this->id);

        $this->registry->getHandler($jobType)->handle($jobType, $id, $payloadBody);

        return $id;
    }

    /**
     * @param string $jobType
     * @param array  $payload
     * @return string
     */
    private function serialize(string $jobType, array $payload): string
    {
        return $this->serializerRegistry->getSerializer($jobType)->serialize($jobType, $payload);
    }
}
