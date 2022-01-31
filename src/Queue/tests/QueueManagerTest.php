<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Core\Container;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueManager;
use Spiral\Queue\Driver\ShortCircuit;

final class QueueManagerTest extends TestCase
{
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
                ]
            ],
            'driverAliases' => [
                'sync' => ShortCircuit::class,
            ],
        ]);

        $container = new Container();
        $container->bind(QueueConfig::class, $config);
        $container->bind(HandlerRegistryInterface::class, m::mock(HandlerRegistryInterface::class));
        $container->bind(FailedJobHandlerInterface::class, m::mock(FailedJobHandlerInterface::class));

        parent::setUp();

        $this->manager = new QueueManager($config, $container);
    }

    public function testGetsDefaultConnection()
    {
        $this->assertInstanceOf(
            ShortCircuit::class,
            $this->manager->getConnection()
        );
    }

    public function testGetsConnectionByNameWithDriverAlias()
    {
        $this->assertInstanceOf(
            ShortCircuit::class,
            $this->manager->getConnection('sync')
        );
    }

    public function testGetsPipelineByAlias()
    {
        $this->assertInstanceOf(
            ShortCircuit::class,
            $queue = $this->manager->getConnection('user-data')
        );

        $this->assertSame($queue, $this->manager->getConnection('sync'));
    }
}
