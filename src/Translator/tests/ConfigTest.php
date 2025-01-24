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
            'locale' => 'ru',
        ]);

        self::assertSame('ru', $config->getDefaultLocale());
    }

    public function testDefaultDomain(): void
    {
        $config = new TranslatorConfig([
            'locale' => 'ru',
        ]);

        self::assertSame('messages', $config->getDefaultDomain());
    }

    public function testFallbackLocale(): void
    {
        $config = new TranslatorConfig([
            'fallbackLocale' => 'ru',
        ]);

        self::assertSame('ru', $config->getFallbackLocale());
    }

    public function testRegisterMessages(): void
    {
        $config = new TranslatorConfig(['autoRegister' => true]);
        self::assertTrue($config->isAutoRegisterMessages());

        $config = new TranslatorConfig(['autoRegister' => false]);
        self::assertFalse($config->isAutoRegisterMessages());

        //Legacy
        $config = new TranslatorConfig(['registerMessages' => true]);
        self::assertTrue($config->isAutoRegisterMessages());
    }

    public function testLocalesDirectory(): void
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/',
        ]);

        self::assertSame('directory/', $config->getLocalesDirectory());
    }

    public function testLocaleDirectory(): void
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory/',
        ]);

        self::assertSame('directory/ru/', $config->getLocaleDirectory('ru'));
    }

    public function testLocaleDirectoryShort(): void
    {
        $config = new TranslatorConfig([
            'directory' => 'directory/',
        ]);

        self::assertSame('directory/ru/', $config->getLocaleDirectory('ru'));
    }

    public function testLocaleDirectoryWithoutSlash(): void
    {
        $config = new TranslatorConfig([
            'localesDirectory' => 'directory',
        ]);
        self::assertSame('directory/en/', $config->getLocaleDirectory('en'));

        $config = new TranslatorConfig([
            'directory' => 'directory',
        ]);
        self::assertSame('directory/en/', $config->getLocaleDirectory('en'));
    }

    public function testLocaleDirectoryWithDirectoryParam(): void
    {
        $config = new TranslatorConfig();

        self::assertSame('directory/en/', $config->getLocaleDirectory('en', 'directory'));
        self::assertSame('directory/en/', $config->getLocaleDirectory('en', 'directory/'));
    }

    public function testLocaleDirectoryLeadingSlash(): void
    {
        $config = new TranslatorConfig([
            'directory' => '/directory/locale',
        ]);

        self::assertSame('/directory/locale/en/', $config->getLocaleDirectory('en'));
    }

    public function testDomains(): void
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*',
                ],
                'messages' => ['*'],
            ],
        ]);

        self::assertSame('spiral', $config->resolveDomain('spiral-views'));
        self::assertSame('messages', $config->resolveDomain('vendor-views'));
    }

    public function testDomainsFallback(): void
    {
        $config = new TranslatorConfig([
            'domains' => [
                'spiral' => [
                    'spiral-*',
                ],
            ],
        ]);

        self::assertSame('external', $config->resolveDomain('external'));
    }

    public function testHasLoader(): void
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class],
        ]);

        self::assertTrue($config->hasLoader('php'));
        self::assertFalse($config->hasLoader('txt'));
    }

    public function testGetLoader(): void
    {
        $config = new TranslatorConfig([
            'loaders' => ['php' => PhpFileLoader::class],
        ]);

        self::assertInstanceOf(LoaderInterface::class, $config->getLoader('php'));
    }

    public function testHasDumper(): void
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class],
        ]);

        self::assertTrue($config->hasDumper('po'));
        self::assertFalse($config->hasDumper('xml'));
    }

    public function testGetDumper(): void
    {
        $config = new TranslatorConfig([
            'dumpers' => ['po' => PoFileDumper::class],
        ]);

        self::assertInstanceOf(DumperInterface::class, $config->getDumper('po'));
    }

    public function testGetDirectories(): void
    {
        $config = new TranslatorConfig();
        self::assertSame([], $config->getDirectories());

        $config = new TranslatorConfig([
            'directories' => [
                'foo',
                'bar/',
            ],
        ]);
        self::assertSame(['foo', 'bar/'], $config->getDirectories());
    }
}
