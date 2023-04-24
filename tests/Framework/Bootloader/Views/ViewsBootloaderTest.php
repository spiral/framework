<?php

declare(strict_types=1);

namespace Framework\Bootloader\Views;

use Spiral\Config\ConfigManager;
use Spiral\Tests\Framework\BaseTestCase;
use Spiral\Views\Bootloader\ViewsBootloader;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\DependencyInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\GlobalVariables;
use Spiral\Views\GlobalVariablesInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewLoader;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

final class ViewsBootloaderTest extends BaseTestCase
{
    public function testViewsInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ViewsInterface::class, ViewManager::class);
    }

    public function testGlobalVariablesRegistryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(GlobalVariablesInterface::class, GlobalVariables::class);
    }

    public function testViewsManagerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ViewManager::class, ViewManager::class);
    }

    public function testLoaderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(LoaderInterface::class, ViewLoader::class);
    }

    public function testAddDirectoryWithNonExistNamespace(): void
    {
        $configs = new ConfigManager($this->createMock(\Spiral\Config\LoaderInterface::class));
        $configs->setDefaults(ViewsConfig::CONFIG, ['namespaces' => []]);

        $bootloader = new ViewsBootloader($configs);
        $bootloader->addDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['bar']], $configs->getConfig(ViewsConfig::CONFIG)['namespaces']);
    }

    public function testAddDirectory(): void
    {
        $configs = new ConfigManager($this->createMock(\Spiral\Config\LoaderInterface::class));
        $configs->setDefaults(ViewsConfig::CONFIG, ['namespaces' => ['foo' => ['baz']]]);

        $bootloader = new ViewsBootloader($configs);
        $bootloader->addDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['baz', 'bar']], $configs->getConfig(ViewsConfig::CONFIG)['namespaces']);
    }

    public function testAddEngine(): void
    {
        $configs = new ConfigManager($this->createMock(\Spiral\Config\LoaderInterface::class));
        $configs->setDefaults(ViewsConfig::CONFIG, ['engines' => []]);

        $bootloader = new ViewsBootloader($configs);
        $bootloader->addEngine('foo');
        $bootloader->addEngine($engine = $this->createMock(EngineInterface::class));

        $this->assertSame(['foo', $engine], $configs->getConfig(ViewsConfig::CONFIG)['engines']);
    }

    public function testAddCacheDependency(): void
    {
        $configs = new ConfigManager($this->createMock(\Spiral\Config\LoaderInterface::class));
        $configs->setDefaults(ViewsConfig::CONFIG, ['dependencies' => []]);

        $bootloader = new ViewsBootloader($configs);
        $bootloader->addCacheDependency('foo');
        $bootloader->addCacheDependency($dependency = $this->createMock(DependencyInterface::class));

        $this->assertSame(['foo', $dependency], $configs->getConfig(ViewsConfig::CONFIG)['dependencies']);
    }
}
