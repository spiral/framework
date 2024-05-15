<?php

declare(strict_types=1);

namespace Framework\Bootloader\Tokenizer;

use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Tests\Framework\BaseTestCase;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;

final class TokenizerBootloaderTest extends BaseTestCase
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
            $this->getContainer()->hasInjector(ClassLocator::class),
        );
    }

    public function testInvocationLocatorInjector(): void
    {
        $this->assertTrue(
            $this->getContainer()->hasInjector(InvocationLocator::class),
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
                    \realpath($this->getDirectoryByAlias('root') . '../vendor/spiral/validator/src'),
                ],
                'exclude' => [
                    $this->getDirectoryByAlias('resources'),
                    $this->getDirectoryByAlias('config'),
                    'tests',
                    'migrations',
                ],
                'cache' => [
                    'directory' => $this->getDirectoryByAlias('runtime') . 'cache/listeners',
                    'enabled' => false,
                ],
                'load' => [
                    'classes' => true,
                    'enums' => false,
                    'interfaces' => false,
                ],
            ],
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
        $configs->setDefaults(TokenizerConfig::CONFIG, ['scopes' => ['bar' => ['directories' => ['baz']]]]);

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->addScopedDirectory('foo', 'bar');

        $this->assertSame(['bar' => ['directories' => ['baz']], 'foo' => ['directories' => ['bar']]],
            $configs->getConfig(TokenizerConfig::CONFIG)['scopes']);
    }

    public function testAddScopedDirectory(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(
            TokenizerConfig::CONFIG,
            ['scopes' => ['foo' => ['exclude' => ['baz'], 'directories' => ['baz']]]],
        );

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->addScopedDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['exclude' => ['baz'], 'directories' => ['baz', 'bar']]],
            $configs->getConfig(TokenizerConfig::CONFIG)['scopes']);
    }

    public function testExcludeScopedDirectoryWithNonExistScope(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TokenizerConfig::CONFIG, ['scopes' => []]);

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->excludeScopedDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['exclude' => ['bar']]],
            $configs->getConfig(TokenizerConfig::CONFIG)['scopes']);
    }

    public function testExcludeScopedDirectory(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(
            TokenizerConfig::CONFIG,
            ['scopes' => ['foo' => ['exclude' => ['baz'], 'directories' => ['baz']]]],
        );

        $bootloader = new TokenizerBootloader($configs);
        $bootloader->excludeScopedDirectory('foo', 'bar');

        $this->assertSame(['foo' => ['exclude' => ['baz', 'bar'], 'directories' => ['baz']]],
            $configs->getConfig(TokenizerConfig::CONFIG)['scopes']);
    }
}
