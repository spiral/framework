<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\BroadcastManager;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Core\FactoryInterface;

final class BroadcastManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|FactoryInterface */
    private $factory;

    private BroadcastManager $manager;

    public function testGetDefaultConnection(): void
    {
        $connection = m::mock(BroadcastInterface::class);

        $this->factory->shouldReceive('make')->once()->with('pusher-driver-class', [
            'driver' => 'pusher-driver-class',
            'foo' => 'bar',
        ])->andReturn($connection);

        self::assertSame($connection, $this->manager->connection());
    }

    public function testGetConnectionByName(): void
    {
        $connection = m::mock(BroadcastInterface::class);

        $this->factory->shouldReceive('make')->once()->with('null-driver-class', [
            'driver' => 'null-driver-class',
            'foo' => 'baz',
        ])->andReturn($connection);

        self::assertSame($connection, $this->manager->connection('null'));
    }

    public function testGetConnectionWithDriverAlias(): void
    {
        $connection = m::mock(BroadcastInterface::class);

        $this->factory->shouldReceive('make')->once()->with('pusher-driver-class', [
            'driver' => 'pusher-driver-class',
            'bar' => 'baz',
        ])->andReturn($connection);

        self::assertSame($connection, $this->manager->connection('inMemory'));
    }

    public function testGetConnectionByAlias(): void
    {
        $connection = m::mock(BroadcastInterface::class);

        $this->factory->shouldReceive('make')->once()->with('pusher-driver-class', [
            'driver' => 'pusher-driver-class',
            'foo' => 'bar',
        ])->andReturn($connection);

        self::assertSame($connection, $this->manager->connection('firebase'));
    }

    public function testConnectionShouldBeCreatedOnlyOnce(): void
    {
        $connection1 = m::mock(BroadcastInterface::class);
        $connection2 = m::mock(BroadcastInterface::class);

        $this->factory->shouldReceive('make')->once()->with('pusher-driver-class', [
            'driver' => 'pusher-driver-class',
            'foo' => 'bar',
        ])->andReturn($connection1);

        self::assertSame($connection1, $this->manager->connection());
        self::assertSame($connection1, $this->manager->connection());

        $this->factory->shouldReceive('make')->once()->with('null-driver-class', [
            'driver' => 'null-driver-class',
            'foo' => 'baz',
        ])->andReturn($connection2);

        self::assertSame($connection2, $this->manager->connection('null'));
        self::assertSame($connection2, $this->manager->connection('null'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $config = new BroadcastConfig([
            'default' => 'log',
            'aliases' => [
                'firebase' => 'log',
            ],
            'driverAliases' => [
                'pusher' => 'pusher-driver-class',
            ],
            'connections' => [
                'log' => [
                    'driver' => 'pusher-driver-class',
                    'foo' => 'bar',
                ],
                'null' => [
                    'driver' => 'null-driver-class',
                    'foo' => 'baz',
                ],
                'inMemory' => [
                    'driver' => 'pusher',
                    'bar' => 'baz',
                ],
            ],
        ]);

        $this->factory = m::mock(FactoryInterface::class);
        $this->manager = new BroadcastManager($this->factory, $config);
    }
}
