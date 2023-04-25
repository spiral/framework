<?php

declare(strict_types=1);

namespace Framework\Bootloader\Scaffolder;

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
            $configs->getConfig(ScaffolderConfig::CONFIG)['defaults']['declarations']
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
                    Declaration\BootloaderDeclaration::TYPE => [
                        'namespace' => 'Bootloader',
                        'postfix' => 'Bootloader',
                        'class' => Declaration\BootloaderDeclaration::class,
                    ],
                    Declaration\ConfigDeclaration::TYPE => [
                        'namespace' => 'Config',
                        'postfix' => 'Config',
                        'class' => Declaration\ConfigDeclaration::class,
                        'options' => [
                            'directory' => $dirs->get('config'),
                        ],
                    ],
                    Declaration\ControllerDeclaration::TYPE => [
                        'namespace' => 'Controller',
                        'postfix' => 'Controller',
                        'class' => Declaration\ControllerDeclaration::class,
                    ],
                    Declaration\FilterDeclaration::TYPE => [
                        'namespace' => 'Filter',
                        'postfix' => 'Filter',
                        'class' => Declaration\FilterDeclaration::class,
                    ],
                    Declaration\MiddlewareDeclaration::TYPE => [
                        'namespace' => 'Middleware',
                        'postfix' => '',
                        'class' => Declaration\MiddlewareDeclaration::class,
                    ],
                    Declaration\CommandDeclaration::TYPE => [
                        'namespace' => 'Command',
                        'postfix' => 'Command',
                        'class' => Declaration\CommandDeclaration::class,
                    ],
                    Declaration\JobHandlerDeclaration::TYPE => [
                        'namespace' => 'Job',
                        'postfix' => 'Job',
                        'class' => Declaration\JobHandlerDeclaration::class,
                    ],
                ],
            ]
        ], $config);
    }
}
