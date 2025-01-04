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

        $this->assertTrue($loader->hasLocale('ru'));
        $this->assertTrue($loader->hasLocale('RU'));
        $this->assertFalse($loader->hasLocale('fr'));
        $this->assertFalse($loader->hasLocale('FR'));

        $loader = new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'directories' => [__DIR__ . '/fixtures/additional'],
        ]));

        $this->assertTrue($loader->hasLocale('ru'));
        $this->assertTrue($loader->hasLocale('RU'));
        $this->assertTrue($loader->hasLocale('fr'));
        $this->assertTrue($loader->hasLocale('FR'));
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

        $this->assertSame($shouldBe, $compared);
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

        $this->assertSame($shouldBe, $compared);
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
        $this->assertInstanceOf(CatalogueInterface::class, $catalogue);
        $this->assertSame('ru', $catalogue->getLocale());

        $catalogue = $loader->loadCatalogue('ru');
        $this->assertInstanceOf(CatalogueInterface::class, $catalogue);
        $this->assertSame('ru', $catalogue->getLocale());

        $this->assertCount(2, $catalogue->getDomains());
        $this->assertContains('messages', $catalogue->getDomains());
        $this->assertContains('views', $catalogue->getDomains());

        $mc = $catalogue->toMessageCatalogue();

        $this->assertTrue($mc->has('message'));
        $this->assertSame('translation', $mc->get('message'));

        $this->assertTrue($mc->has('Welcome To Spiral', 'views'));
        $this->assertSame(
            'Добро пожаловать в Spiral Framework',
            $mc->get('Welcome To Spiral', 'views')
        );

        $this->assertTrue($mc->has('Twig Version', 'views'));
        $this->assertSame(
            'Twig версия',
            $mc->get('Twig Version', 'views')
        );

        $this->assertFalse($loader->hasLocale('fr'));
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
        $this->assertTrue($mc->has('Welcome To Spiral', 'views'));
        $this->assertSame(
            'Bienvenue à Spirale',
            $mc->get('Welcome To Spiral', 'views')
        );

        $this->assertTrue($loader->hasLocale('fr'));
        $this->assertTrue($loader->hasLocale('FR'));
        $this->assertTrue($loader->hasLocale('ru'));
        $this->assertTrue($loader->hasLocale('RU'));
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

        $this->assertTrue($mc->has('should_be_override'));
        $this->assertSame('changed by application translation', $mc->get('should_be_override'));
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
        $this->assertInstanceOf(CatalogueInterface::class, $catalogue);
        $this->assertSame('ru', $catalogue->getLocale());

        $catalogue = $loader->loadCatalogue('ru');
        $this->assertInstanceOf(CatalogueInterface::class, $catalogue);
        $this->assertSame('ru', $catalogue->getLocale());

        $this->assertCount(1, $catalogue->getDomains());
        $this->assertContains('messages', $catalogue->getDomains());
        $this->assertNotContains('views', $catalogue->getDomains());
    }

    public function testStaticLoader(): void
    {
        $loader = new RuntimeLoader();
        $this->assertFalse($loader->hasLocale('en'));
    }

    public function testStaticLoaderException(): void
    {
        $this->expectException(LocaleException::class);

        $loader = new RuntimeLoader();
        $loader->loadCatalogue('en');
    }
}
