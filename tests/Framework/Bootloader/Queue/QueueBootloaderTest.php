<?php

declare(strict_types=1);

namespace Framework\Bootloader\Queue;

use Spiral\App\TestApp;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueManager;
use Spiral\Queue\QueueRegistry;
use Spiral\Queue\SerializerRegistry;
use Spiral\Queue\SerializerRegistryInterface;
use Spiral\Tests\Framework\BaseTest;

final class QueueBootloaderTest extends BaseTest
{
    private TestApp $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp();
    }

    public function testHandlerRegistryInterfaceBinding(): void
    {
        $this->assertInstanceOf(HandlerRegistryInterface::class, $this->app->get(QueueRegistry::class));
    }

    public function testFailedJobHandlerInterfaceBinding(): void
    {
        $this->assertInstanceOf(FailedJobHandlerInterface::class, $this->app->get(LogFailedJobHandler::class));
    }

    public function testQueueConnectionProviderInterfaceBinding(): void
    {
        $this->assertInstanceOf(QueueConnectionProviderInterface::class, $this->app->get(QueueManager::class));
    }

    public function testSerializerRegistryInterfaceBinding(): void
    {
        $this->assertInstanceOf(SerializerRegistryInterface::class, $this->app->get(SerializerRegistry::class));
    }

    public function testQueueManagerBinding(): void
    {
        $this->assertInstanceOf(QueueManager::class, $this->app->get(QueueManager::class));
    }

    public function testQueueRegistryBinding(): void
    {
        $this->assertInstanceOf(QueueRegistry::class, $this->app->get(QueueRegistry::class));
    }

    public function testSerializerRegistryBinding(): void
    {
        $this->assertInstanceOf(SerializerRegistry::class, $this->app->get(SerializerRegistry::class));
    }

    public function testConfig(): void
    {
        $this->assertSame([
            'default' => 'sync',
            'connections' => [
                'sync' => ['driver' => 'sync'],
            ],

            'registry' => [
                'handlers' => [],
                'serializers' => [],
            ],
            'driverAliases' => [
                'sync' => \Spiral\Queue\Driver\SyncDriver::class,
                'null' => \Spiral\Queue\Driver\NullDriver::class,
            ],
        ], $this->app->get(QueueConfig::class)->toArray());
    }

    public function testRegisterDriverAlias(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(QueueConfig::CONFIG, ['driverAliases' => []]);

        $bootloader = new QueueBootloader($configs);
        $bootloader->registerDriverAlias('foo', 'bar');

        $this->assertSame([
            'bar' => 'foo'
        ], $configs->getConfig(QueueConfig::CONFIG)['driverAliases']);
    }
}
