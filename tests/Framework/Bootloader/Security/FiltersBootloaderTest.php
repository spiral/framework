<?php

declare(strict_types=1);

namespace Framework\Bootloader\Security;

use Spiral\Bootloader\Security\FiltersBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\PopulateDataFromEntityInterceptor;
use Spiral\Filters\Model\Interceptor\ValidateFilterInterceptor;
use Spiral\Filters\InputInterface;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

final class FiltersBootloaderTest extends BaseTestCase
{
    public function testFilterProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(FilterProviderInterface::class, FilterProvider::class);
    }

    #[TestScope(Spiral::HttpRequest)]
    public function testInputInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(InputInterface::class, InputScope::class);
    }

    public function testFiltersInjector(): void
    {
        self::assertTrue($this->getContainer()->hasInjector(FilterInterface::class));
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(FiltersConfig::CONFIG, [
            'interceptors' => [
                PopulateDataFromEntityInterceptor::class,
                ValidateFilterInterceptor::class,
            ],
        ]);
    }

    public function testAddInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(FiltersConfig::CONFIG, ['interceptors' => []]);

        $container = $this->getContainer();
        $bootloader = new FiltersBootloader($container, $container, $configs);
        $bootloader->addInterceptor('foo');
        $bootloader->addInterceptor('bar');

        self::assertSame([
            'foo', 'bar',
        ], $configs->getConfig(FiltersConfig::CONFIG)['interceptors']);
    }
}
