<?php

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\KernelInterface;
use Spiral\Bootloader\ServerBootloader;
use Spiral\Core\Container;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\Jobs\Queue\Decorator;
use Spiral\Jobs\QueueInterface as QueueBridgeInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\Queue;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Serializer\DefaultSerializer;
use Spiral\RoadRunner\Jobs\Serializer\SerializerInterface;
use Spiral\RoadRunner\WorkerInterface;

final class JobsBootloader extends Bootloader
{
    /**
     * @var array<class-string>
     */
    protected const DEPENDENCIES = [
        ServerBootloader::class,
    ];

    /**
     * @param Container $container
     * @param KernelInterface $kernel
     * @param JobDispatcher $jobs
     * @return void
     */
    public function boot(Container $container, KernelInterface $kernel, JobDispatcher $jobs): void
    {
        $kernel->addDispatcher($jobs);

        if (!$container->has(SerializerInterface::class)) {
            $this->registerJobsSerializer($container);
        }

        $this->registerJobs($container);
        $this->registerConsumer($container);
        $this->registerQueue($container);

        // Compatibility Bridges
        $this->registerQueueBridge($container);
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerQueueBridge(Container $container): void
    {
        $container->bindSingleton(
            Decorator::class,
            static function (JobsInterface $jobs, QueueInterface $queue): Decorator {
                return new Decorator($jobs, $queue);
            }
        );

        $container->bindSingleton(
            QueueBridgeInterface::class,
            static function (Decorator $decorator): QueueBridgeInterface {
                return $decorator;
            }
        );
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerJobsSerializer(Container $container): void
    {
        $container->bindSingleton(SerializerInterface::class, static function () {
            return new DefaultSerializer();
        });
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerConsumer(Container $container): void
    {
        $container->bindSingleton(
            Consumer::class,
            static function (WorkerInterface $worker, SerializerInterface $serializer): Consumer {
                return new Consumer($worker, $serializer);
            }
        );

        $container->bindSingleton(
            ConsumerInterface::class,
            static function (Consumer $consumer): ConsumerInterface {
                return $consumer;
            }
        );
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerJobs(Container $container): void
    {
        $container->bindSingleton(
            Jobs::class,
            static function (RPCInterface $rpc, SerializerInterface $serializer): Jobs {
                return new Jobs($rpc, $serializer);
            }
        );


        $container->bindSingleton(
            JobsInterface::class,
            static function (Jobs $jobs): JobsInterface {
                return $jobs;
            }
        );
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerQueue(Container $container): void
    {
        $container->bindSingleton(Queue::class, static function (JobsInterface $jobs): Queue {
            foreach ($jobs as $queue) {
                return $queue;
            }

            throw new \OutOfBoundsException('No available queues were registered');
        });

        $container->bindSingleton(QueueInterface::class, static function (Queue $queue): QueueInterface {
            return $queue;
        });
    }
}
