<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\RuntimeLoader;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Exception\LocaleException;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class LoaderTest extends TestCase
{
    public function testHasLocale(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
        ]));

        self::assertTrue($loader->hasLocale('ru'));
        self::assertTrue($loader->hasLocale('RU'));
        self::assertFalse($loader->hasLocale('fr'));
        self::assertFalse($loader->hasLocale('FR'));

        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'directories' => [__DIR__ . '/fixtures/additional'],
        ]));

        self::assertTrue($loader->hasLocale('ru'));
        self::assertTrue($loader->hasLocale('RU'));
        self::assertTrue($loader->hasLocale('fr'));
        self::assertTrue($loader->hasLocale('FR'));
    }

    public function testGetLocales(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
        ]));

        $compared = $loader->getLocales();
        $shouldBe = ['en', 'ru'];
        sort($shouldBe);
        sort($compared);

        self::assertSame($shouldBe, $compared);
    }

    public function testGetLocalesWithAdditionalDirectories(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'directories' => [__DIR__ . '/fixtures/additional'],
        ]));

        $compared = $loader->getLocales();
        $shouldBe = ['en', 'ru', 'fr'];
        sort($shouldBe);
        sort($compared);

        self::assertSame($shouldBe, $compared);
    }

    public function testLoadCatalogue(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ]));

        $catalogue = $loader->loadCatalogue('RU');
        self::assertInstanceOf(CatalogueInterface::class, $catalogue);
        self::assertSame('ru', $catalogue->getLocale());

        $catalogue = $loader->loadCatalogue('ru');
        self::assertInstanceOf(CatalogueInterface::class, $catalogue);
        self::assertSame('ru', $catalogue->getLocale());

        self::assertCount(2, $catalogue->getDomains());
        self::assertContains('messages', $catalogue->getDomains());
        self::assertContains('views', $catalogue->getDomains());

        $mc = $catalogue->toMessageCatalogue();

        self::assertTrue($mc->has('message'));
        self::assertSame('translation', $mc->get('message'));

        self::assertTrue($mc->has('Welcome To Spiral', 'views'));
        self::assertSame('Добро пожаловать в Spiral Framework', $mc->get('Welcome To Spiral', 'views'));

        self::assertTrue($mc->has('Twig Version', 'views'));
        self::assertSame('Twig версия', $mc->get('Twig Version', 'views'));

        self::assertFalse($loader->hasLocale('fr'));
    }

    public function testLoadCatalogueWithAdditionalDirectories(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'directories' => [__DIR__ . '/fixtures/additional'],
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ]));

        $catalogue = $loader->loadCatalogue('fr');
        $mc = $catalogue->toMessageCatalogue();
        self::assertTrue($mc->has('Welcome To Spiral', 'views'));
        self::assertSame('Bienvenue à Spirale', $mc->get('Welcome To Spiral', 'views'));

        self::assertTrue($loader->hasLocale('fr'));
        self::assertTrue($loader->hasLocale('FR'));
        self::assertTrue($loader->hasLocale('ru'));
        self::assertTrue($loader->hasLocale('RU'));
    }

    public function testApplicationTranslationShouldOverrideAdditionalTranslations(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'directories' => [__DIR__ . '/fixtures/additional'],
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ]));

        $catalogue = $loader->loadCatalogue('ru');
        $mc = $catalogue->toMessageCatalogue();

        self::assertTrue($mc->has('should_be_override'));
        self::assertSame('changed by application translation', $mc->get('should_be_override'));
    }

    public function testLoadCatalogueNoLoader(): void
    {
        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
            ],
        ]));

        $catalogue = $loader->loadCatalogue('RU');
        self::assertInstanceOf(CatalogueInterface::class, $catalogue);
        self::assertSame('ru', $catalogue->getLocale());

        $catalogue = $loader->loadCatalogue('ru');
        self::assertInstanceOf(CatalogueInterface::class, $catalogue);
        self::assertSame('ru', $catalogue->getLocale());

        self::assertCount(1, $catalogue->getDomains());
        self::assertContains('messages', $catalogue->getDomains());
        self::assertNotContains('views', $catalogue->getDomains());
    }

    public function testStaticLoader(): void
    {
        $loader = new RuntimeLoader();
        self::assertFalse($loader->hasLocale('en'));
    }

    public function testStaticLoaderException(): void
    {
        $this->expectException(LocaleException::class);

        $loader = new RuntimeLoader();
        $loader->loadCatalogue('en');
    }
}
