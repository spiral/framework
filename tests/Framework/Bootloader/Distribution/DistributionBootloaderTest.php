<?php

declare(strict_types=1);

namespace Framework\Bootloader\Distribution;

use Mockery as m;
use Spiral\Distribution\Config\DistributionConfig;
use Spiral\Distribution\DistributionInterface;
use Spiral\Distribution\Manager;
use Spiral\Distribution\MutableDistributionInterface;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class DistributionBootloaderTest extends BaseTestCase
{
    public function testUriResolverInterfaceBinding(): void
    {
        $dist = $this->mockContainer(DistributionInterface::class);
        $dist->shouldReceive('resolver')->once()->andReturn(m::mock(UriResolverInterface::class));

        $this->assertContainerBoundAsSingleton(UriResolverInterface::class, UriResolverInterface::class);
    }

    public function testDistributionInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(DistributionInterface::class, Manager::class);
    }

    public function testMutableDistributionInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(MutableDistributionInterface::class, DistributionInterface::class);
    }

    public function testManagerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(Manager::class, DistributionInterface::class);
    }

    public function testDistributionConfigInjector(): void
    {
        self::assertTrue($this->getContainer()->hasInjector(DistributionConfig::class));
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(DistributionConfig::CONFIG, [
            'default' => Manager::DEFAULT_RESOLVER,
            'resolvers' => []
        ]);
    }
}
