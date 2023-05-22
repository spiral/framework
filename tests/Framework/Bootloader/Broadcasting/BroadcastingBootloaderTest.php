<?php

declare(strict_types=1);

namespace Framework\Bootloader\Broadcasting;

use Spiral\Broadcasting\Bootloader\BroadcastingBootloader;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\BroadcastManager;
use Spiral\Broadcasting\BroadcastManagerInterface;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Broadcasting\Driver\NullBroadcast;
use Spiral\Broadcasting\TopicRegistry;
use Spiral\Broadcasting\TopicRegistryInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class BroadcastingBootloaderTest extends BaseTestCase
{
    public const ENV = [
        'BROADCAST_CONNECTION' => 'null',
        'BROADCAST_AUTHORIZE_PATH' => '/ws'
    ];

    public function testBroadcastManagerInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(BroadcastManagerInterface::class, BroadcastManager::class);
    }

    public function testBroadcastInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(BroadcastInterface::class, NullBroadcast::class);
    }

    public function testTopicRegistryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(TopicRegistryInterface::class, TopicRegistry::class);
    }

    public function testConfig(): void
    {
        $config = $this->getConfig(BroadcastConfig::CONFIG);

        $this->assertSame('null', $config['default']);
        $this->assertSame('/ws', $config['authorize']['path']);
    }

    public function testRegisterDriverAlias(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(BroadcastConfig::CONFIG, ['driverAliases' => []]);

        $bootloader = new BroadcastingBootloader($configs);
        $bootloader->registerDriverAlias('foo', 'bar');

        $this->assertSame(['bar' => 'foo'], $configs->getConfig(BroadcastConfig::CONFIG)['driverAliases']);
    }
}
