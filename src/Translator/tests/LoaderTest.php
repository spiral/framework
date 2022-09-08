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
        $this->assertTrue(in_array('messages', $catalogue->getDomains()));
        $this->assertTrue(in_array('views', $catalogue->getDomains()));

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
        $this->assertTrue(in_array('messages', $catalogue->getDomains()));
        $this->assertFalse(in_array('views', $catalogue->getDomains()));
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
