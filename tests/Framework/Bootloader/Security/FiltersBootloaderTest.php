<?php

declare(strict_types=1);

namespace Framework\Bootloader\Security;

use Spiral\Bootloader\Security\FiltersBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Dto\FilterInterface;
use Spiral\Filters\Dto\FilterProvider;
use Spiral\Filters\Dto\FilterProviderInterface;
use Spiral\Filters\Dto\Interceptor\AuthorizeFilterInterceptor;
use Spiral\Filters\Dto\Interceptor\PopulateDataFromEntityInterceptor;
use Spiral\Filters\Dto\Interceptor\ValidateFilterInterceptor;
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
                PopulateDataFromEntityInterceptor::class,
                ValidateFilterInterceptor::class,
                AuthorizeFilterInterceptor::class,
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
