<?php

namespace Spiral\Tests\Queue\Core;

use Mockery as m;
use ReflectionClass;
use RuntimeException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Core\QueueInjector;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueManager;
use Spiral\Tests\Queue\Core\Stub\TestQueueClass;
use Spiral\Tests\Queue\TestCase;

final class QueueInjectorTest extends TestCase
{
    private ?QueueInterface $defaultQueue = null;

    public function testGetByContext(): void
    {
        $injector = $this->createInjector();
        $reflection = new ReflectionClass(TestQueueClass::class);

        $result = $injector->createInjection($reflection, 'test');

        $this->assertInstanceOf(TestQueueClass::class, $result);
    }

    public function testGetByIncorrectContext(): void
    {
        $injector = $this->createInjector();
        $reflection = new ReflectionClass(QueueInterface::class);

        $result = $injector->createInjection($reflection, 'userQueue');

        // The default connection should be returned
        $this->assertSame($this->defaultQueue, $result);
    }

    public function testBadArgumentTypeException(): void
    {
        $injector = $this->createInjector();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The queue obtained by the context');

        $reflection = new ReflectionClass(TestQueueClass::class);
        $injector->createInjection($reflection, 'queue');
    }

    private function createInjector(): QueueInjector
    {
        $this->defaultQueue = m::mock(QueueInterface::class);
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
            $result = [
                    'sync' => $this->defaultQueue,
                    'test' => new TestQueueClass(),
                ][$name] ?? null;
            if ($result === null) {
                throw new NotFoundException();
            }
            return $result;
        });
        $manager = new QueueManager($config, $factory);

        return new QueueInjector($manager, $config);
    }
}
