<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\Config\TranslatorConfig;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;

class ConfigTest extends TestCase
{
    public function testDefaultLocale(): void
    {
        $config = new TranslatorConfig([
            'locale' => 'ru'
        ]);

        $this->assertSame('ru', $config->getDefaultLocale());
    }

    public function testDefaultDomain(): void
    {
        $config = new TranslatorConfig([
            'locale' => 'ru'
        ]);

        $this->assertSame('messages', $config->getDefaultDomain());
    }

    public function testFallbackLocale(): void
    {
        $config = new TranslatorConfig([
            'fallbackLocale' => 'ru'
        ]);

        $this->assertSame('ru', $config->getFallbackLocale());
    }

    public function testRegisterMessages(): void
    {
        $config = new TranslatorConfig(['autoRegister' => true]);
        $this->assertTrue($config->isAutoRegisterMessages());

        $config = new TranslatorConfig(['autoRegister' => false]);
        $this->assertFalse($config->isAutoRegisterMessages());

        //Legacy
        $config = new TranslatorConfig(['registerMessages' => true]);
        $this->assertTrue($config->isAutoRegisterMessages());
    }

    public function testLocalesDirectory(): void
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/'
        ]);

        $this->assertSame('directory/', $config->getLocalesDirectory());
    }

    public function testLocaleDirectory(): void
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/'
        ]);

        $this->assertSame('directory/ru/', $config->getLocaleDirectory('ru'));
    }

    public function testLocaleDirectoryShort(): void
    {
        $config = new TranslatorConfig([
            'directory' => 'directory/'
        ]);

        $this->assertSame('directory/ru/', $config->getLocaleDirectory('ru'));
    }

    public function testLocaleDirectoryWithoutSlash(): void
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory'
        ]);
        $this->assertSame('directory/en/', $config->getLocaleDirectory('en'));

        $config = new TranslatorConfig([
            'directory' => 'directory'
        ]);
        $this->assertSame('directory/en/', $config->getLocaleDirectory('en'));
    }

    public function testLocaleDirectoryWithDirectoryParam(): void
    {
        $config = new TranslatorConfig();

        $this->assertSame('directory/en/', $config->getLocaleDirectory('en', 'directory'));
        $this->assertSame('directory/en/', $config->getLocaleDirectory('en', 'directory/'));
    }

    public function testDomains(): void
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]);

        $this->assertSame('spiral', $config->resolveDomain('spiral-views'));
        $this->assertSame('messages', $config->resolveDomain('vendor-views'));
    }

    public function testDomainsFallback(): void
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral' => [
                    'spiral-*'
                ]
            ]
        ]);

        $this->assertSame('external', $config->resolveDomain('external'));
    }

    public function testHasLoader(): void
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class]
        ]);

        $this->assertTrue($config->hasLoader('php'));
        $this->assertFalse($config->hasLoader('txt'));
    }

    public function testGetLoader(): void
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class]
        ]);

        $this->assertInstanceOf(LoaderInterface::class, $config->getLoader('php'));
    }

    public function testHasDumper(): void
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class]
        ]);

        $this->assertTrue($config->hasDumper('po'));
        $this->assertFalse($config->hasDumper('xml'));
    }

    public function testGetDumper(): void
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class]
        ]);

        $this->assertInstanceOf(DumperInterface::class, $config->getDumper('po'));
    }

    public function testGetDirectories(): void
    {
        $config = new TranslatorConfig();
        $this->assertSame([], $config->getDirectories());

        $config = new TranslatorConfig([
            'directories' => [
                'foo',
                'bar/'
            ]
        ]);
        $this->assertSame(['foo', 'bar/'], $config->getDirectories());
    }
}
