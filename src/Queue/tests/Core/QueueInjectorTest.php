<?php

namespace Spiral\Tests\Queue\Core;

use Mockery as m;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Core\QueueInjector;
use Spiral\Queue\Queue;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueManager;
use Spiral\Tests\Queue\Core\Stub\TestQueueClass;
use Spiral\Tests\Queue\TestCase;

final class QueueInjectorTest extends TestCase
{
    private QueueInterface $defaultQueue;
    private QueueInterface $testQueue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testQueue = m::mock(QueueInterface::class);
        $this->defaultQueue = m::mock(QueueInterface::class);
    }

    public function testGetByContext(): void
    {
        $injector = $this->createInjector();
        $reflection = new ReflectionClass(TestQueueClass::class);

        $this->testQueue->shouldReceive('push')->once();

        $result = $injector->createInjection($reflection, 'test');
        $result->push('foo');

        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testGetByIncorrectContext(): void
    {
        $injector = $this->createInjector();
        $reflection = new ReflectionClass(QueueInterface::class);

        $this->defaultQueue->shouldReceive('push')->once();

        $result = $injector->createInjection($reflection, 'userQueue');
        $result->push('foo');
    }

    private function createInjector(): QueueInjector
    {

        $config = new QueueConfig([
            'default' => 'sync',
            'aliases' => [
                'mail-queue' => 'roadrunner',
                'rating-queue' => 'sync',
                'queue' => 'sync',
                'test' => 'test',
            ],
            'driverAliases' => [],
            'connections' => [
                'sync' => [
                    'driver' => 'sync',
                ],
                'test' => [
                    'driver' => 'test',
                ],
                'roadrunner' => [
                    'driver' => 'roadrunner',
                    'default' => 'local',
                    'pipelines' => [
                        'local' => [
                            'connector' => m::mock(QueueInterface::class),
                            'consume' => true,
                        ],
                    ],
                ],
            ],
        ]);
        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->andReturnUsing(function (string $name): QueueInterface {
            $result = ['sync' => $this->defaultQueue, 'test' => $this->testQueue][$name] ?? null;

            if ($result === null) {
                throw new NotFoundException();
            }
            return $result;
        });

        $container = m::mock(ContainerInterface::class);

        return new QueueInjector(
            new QueueManager(
                $config,
                $container,
                $factory
            )
        );
    }
}
