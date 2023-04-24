<?php

declare(strict_types=1);

namespace Framework\Bootloader\Boot;

use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\ConfigsInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class ConfigurationBootloaderTest extends BaseTestCase
{
    public function testConfigsInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ConfigsInterface::class, ConfigManager::class);
    }

    public function testConfiguratorInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ConfiguratorInterface::class, ConfigManager::class);
    }

    public function testConfigManagerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ConfigManager::class, ConfigManager::class);
    }
}
