<?php

declare(strict_types=1);

namespace Framework\Bootloader\Security;

use Spiral\Bootloader\Security\FiltersBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\FilterProvider;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;
use Spiral\Tests\Framework\BaseTest;

final class FiltersBootloaderTest extends BaseTest
{
    public function testFilterProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(FilterProviderInterface::class, FilterProvider::class);
    }

    public function testInputInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(InputInterface::class, InputScope::class);
    }

    public function testFiltersInjector(): void
    {
        $this->assertTrue(
            $this->getContainer()->hasInjector(FilterInterface::class)
        );
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(FiltersConfig::CONFIG, [
            'interceptors' => [
                \Spiral\Filters\Interceptors\PopulateDataFromEntityInterceptor::class,
                \Spiral\Filters\Interceptors\ValidateFilterInterceptor::class,
                \Spiral\Filters\Interceptors\AuthorizeFilterInterceptor::class,
            ],
        ]);
    }

    public function testAddInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(FiltersConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new FiltersBootloader($this->getContainer(), $configs);
        $bootloader->addInterceptor('foo');
        $bootloader->addInterceptor('bar');

        $this->assertSame([
            'foo', 'bar'
        ], $configs->getConfig(FiltersConfig::CONFIG)['interceptors']);
    }
}
