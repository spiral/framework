<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueManager;
use Spiral\Queue\Driver\SyncDriver;

final class QueueManagerTest extends TestCase
{
    private m\MockInterface|FactoryInterface $factory;
    private QueueManager $manager;

    public function testGetsDefaultConnection(): void
    {
        $this->factory->shouldReceive('make')
            ->once()
            ->with(SyncDriver::class, ['driver' => SyncDriver::class])
            ->andReturn(\Mockery::mock(QueueInterface::class));

        $this->manager->getConnection();
    }

    public function testGetsConnectionByNameWithDriverAlias(): void
    {
        $this->factory->shouldReceive('make')
            ->once()
            ->with(SyncDriver::class, ['driver' => SyncDriver::class])
            ->andReturn(\Mockery::mock(QueueInterface::class));

        $this->manager->getConnection('sync');
    }

    public function testGetsPipelineByAlias(): void
    {
        $this->factory->shouldReceive('make')
            ->once()
            ->with(SyncDriver::class, ['driver' => SyncDriver::class])
            ->andReturn(\Mockery::mock(QueueInterface::class));

        $queue = $this->manager->getConnection('user-data');

        self::assertSame($queue, $this->manager->getConnection('sync'));
    }

    protected function setUp(): void
    {
        $config = new QueueConfig([
            'default' => 'sync',
            'aliases' => [
                'user-data' => 'sync',
            ],
            'connections' => [
                'sync' => [
                    'driver' => 'sync',
                ],
            ],
            'driverAliases' => [
                'sync' => SyncDriver::class,
            ],
        ]);

        $container = new Container();
        $container->bind(CoreInterface::class, m::mock(CoreInterface::class));

        $this->factory = \Mockery::mock(FactoryInterface::class);

        parent::setUp();

        $this->manager = new QueueManager($config, $container, $this->factory);
    }
}
