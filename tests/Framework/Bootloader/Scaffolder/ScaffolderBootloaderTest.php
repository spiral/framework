<?php

declare(strict_types=1);

namespace Framework\Bootloader\Scaffolder;

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Tests\Framework\BaseTest;

final class ScaffolderBootloaderTest extends BaseTest
{
    public function testSlugifyInterfaceBinding(): void
    {
        $this->assertContainerBound(SlugifyInterface::class, Slugify::class);
    }

    public function testAddDeclaration(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ScaffolderConfig::CONFIG, ['declarations' => []]);

        $bootloader = new ScaffolderBootloader($configs, $this->createMock(KernelInterface::class));
        $bootloader->addDeclaration('foo', ['bar' => 'baz']);

        $this->assertSame(['foo' => ['bar' => 'baz']], $configs->getConfig(ScaffolderConfig::CONFIG)['declarations']);
    }
}
