<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Goridge\RPC;
use Spiral\Jobs\Exception\JobException;
use Spiral\RoadRunner\Exception\RoadRunnerException;

final class Queue implements QueueInterface, SingletonInterface
{
    // RoadRunner jobs service
    private const RR_SERVICE = 'jobs';

    /** @var RPC */
    private $rpc;

    /** @var SerializerRegistryInterface */
    private $serializerRegistry;

    /** @var \Doctrine\Inflector\Inflector */
    private $inflector;

    /**
     * @param RPC                         $rpc
     * @param SerializerRegistryInterface $registry
     */
    public function __construct(RPC $rpc, SerializerRegistryInterface $registry)
    {
        $this->rpc = $rpc;
        $this->serializerRegistry = $registry;
        $this->inflector = (new \Doctrine\Inflector\Rules\English\InflectorFactory())->build();
    }

    /**
     * Schedule job of a given type.
     *
     * @param string       $jobType
     * @param array        $payload
     * @param Options|null $options
     * @return string
     *
     * @throws JobException
     */
    public function push(string $jobType, array $payload = [], Options $options = null): string
    {
        try {
            return $this->rpc->call(self::RR_SERVICE . '.Push', [
                'job'     => $this->jobName($jobType),
                'payload' => $this->serialize($jobType, $payload),
                'options' => $options ?? new Options()
            ]);
        } catch (RoadRunnerException | \Throwable $e) {
            throw new JobException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Schedule job of a given type.
     *
     * @param string       $jobType
     * @param array        $payload
     * @param Options|null $options
     * @return bool
     *
     * @throws JobException
     */
    public function pushAsync(string $jobType, array $payload = [], Options $options = null): bool
    {
        try {
            return $this->rpc->call(self::RR_SERVICE . '.PushAsync', [
                'job'     => $this->jobName($jobType),
                'payload' => $this->serialize($jobType, $payload),
                'options' => $options ?? new Options()
            ]);
        } catch (RoadRunnerException | \Throwable $e) {
            throw new JobException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $job
     * @return string
     */
    private function jobName(string $job): string
    {
        $names = explode('\\', $job);
        $names = array_map(function (string $value) {
            return $this->inflector->camelize($value);
        }, $names);

        return join('.', $names);
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
