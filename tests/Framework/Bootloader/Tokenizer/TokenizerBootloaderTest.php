<?php

declare(strict_types=1);

namespace Framework\Bootloader\Tokenizer;

use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Tests\Framework\BaseTest;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;

final class TokenizerBootloaderTest extends BaseTest
{
    public function testScopedClassesInterfaceBinding(): void
    {
        $this->assertContainerBound(ScopedClassesInterface::class, ScopedClassLocator::class);
    }

    public function testClassesInterfaceBinding(): void
    {
        $this->assertContainerBound(ClassesInterface::class, ClassLocator::class);
    }

    public function testInvocationsInterfaceBinding(): void
    {
        $this->assertContainerBound(InvocationsInterface::class, InvocationLocator::class);
    }

    public function testClassLocatorInjector(): void
    {
        $this->assertTrue(
            $this->getContainer()->hasInjector(ClassLocator::class)
        );
    }

    public function testInvocationLocatorInjector(): void
    {
        $this->assertTrue(
            $this->getContainer()->hasInjector(InvocationLocator::class)
        );
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(
            TokenizerConfig::CONFIG,
            [
                'debug' => false,
                'directories' => [
                    $this->getDirectoryByAlias('app'),
                ],
                'exclude' => [
                    $this->getDirectoryByAlias('resources'),
                    $this->getDirectoryByAlias('config'),
                    'tests',
                    'migrations',
                ],
            ]
        );
    }

    public function testAddDirectory(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TokenizerConfig::CONFIG, ['directories' => []]);

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->addDirectory('foo');

        $this->assertSame(['foo'], $configs->getConfig(TokenizerConfig::CONFIG)['directories']);
    }

    public function testAddScopedDirectoryWithNonExistScope(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TokenizerConfig::CONFIG, ['scopes' => []]);

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->addScopedDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['bar']], $configs->getConfig(TokenizerConfig::CONFIG)['scopes']);
    }

    public function testAddScopedDirectory(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TokenizerConfig::CONFIG, ['scopes' => ['foo' => ['baz']]]);

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->addScopedDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['baz', 'bar']], $configs->getConfig(TokenizerConfig::CONFIG)['scopes']);
    }
}
