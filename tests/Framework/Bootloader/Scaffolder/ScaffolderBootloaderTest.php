<?php

declare(strict_types=1);

namespace Framework\Bootloader\Scaffolder;

use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Spiral\Scaffolder\Declaration\ConfigDeclaration;
use Spiral\Scaffolder\Declaration\ControllerDeclaration;
use Spiral\Scaffolder\Declaration\FilterDeclaration;
use Spiral\Scaffolder\Declaration\MiddlewareDeclaration;
use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Spiral\Scaffolder\Declaration\JobHandlerDeclaration;
use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Scaffolder\Declaration;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Tests\Framework\BaseTestCase;

final class ScaffolderBootloaderTest extends BaseTestCase
{
    public function testSlugifyInterfaceBinding(): void
    {
        $this->assertContainerBound(SlugifyInterface::class, Slugify::class);
    }

    public function testAddDeclaration(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ScaffolderConfig::CONFIG, ['defaults' => ['declarations' => []]]);

        $bootloader = new ScaffolderBootloader($configs, $this->createMock(KernelInterface::class));
        $bootloader->addDeclaration('foo', ['bar' => 'baz']);

        $this->assertSame(
            ['foo' => ['bar' => 'baz']],
            $configs->getConfig(ScaffolderConfig::CONFIG)['defaults']['declarations'],
        );
    }

    public function testDefaultConfig(): void
    {
        $config = $this->getConfig(ScaffolderConfig::CONFIG);
        $dirs = $this->getContainer()->get(DirectoriesInterface::class);

        $this->assertSame([
            'header' => [],
            'directory' => $dirs->get('app') . 'src/',
            'namespace' => 'Spiral\\App',
            'declarations' => [],
            'defaults' => [
                'declarations' => [
                    BootloaderDeclaration::TYPE => [
                        'namespace' => 'Bootloader',
                        'postfix' => 'Bootloader',
                        'class' => BootloaderDeclaration::class,
                    ],
                    ConfigDeclaration::TYPE => [
                        'namespace' => 'Config',
                        'postfix' => 'Config',
                        'class' => ConfigDeclaration::class,
                        'options' => [
                            'directory' => $dirs->get('config'),
                        ],
                    ],
                    ControllerDeclaration::TYPE => [
                        'namespace' => 'Controller',
                        'postfix' => 'Controller',
                        'class' => ControllerDeclaration::class,
                    ],
                    FilterDeclaration::TYPE => [
                        'namespace' => 'Filter',
                        'postfix' => 'Filter',
                        'class' => FilterDeclaration::class,
                    ],
                    MiddlewareDeclaration::TYPE => [
                        'namespace' => 'Middleware',
                        'postfix' => 'Middleware',
                        'class' => MiddlewareDeclaration::class,
                    ],
                    CommandDeclaration::TYPE => [
                        'namespace' => 'Command',
                        'postfix' => 'Command',
                        'class' => CommandDeclaration::class,
                    ],
                    JobHandlerDeclaration::TYPE => [
                        'namespace' => 'Job',
                        'postfix' => 'Job',
                        'class' => JobHandlerDeclaration::class,
                    ],
                ],
            ],
        ], $config);
    }
}
