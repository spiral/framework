<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\MemoryInterface;
use Spiral\Core\NullMemory;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\Catalogue\RuntimeLoader;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class PluralizeTest extends TestCase
{
    public function testPluralize(): void
    {
        $this->assertSame(
            '1 dog',
            $this->translator()->transChoice('{n} dog|{n} dogs', 1)
        );

        $this->assertSame(
            '2 dogs',
            $this->translator()->transChoice('{n} dog|{n} dogs', 2)
        );

        $this->assertSame(
            '2221 dogs',
            $this->translator()->transChoice('{n} dog|{n} dogs', 2220, ['n' => 2221])
        );

        $this->assertSame(
            '2221 dog',
            $this->translator()->transChoice('{n} dog|{n} dogs', 1, ['n' => 2221])
        );
    }

    public function testInterpolation(): void
    {
        $this->assertSame(
            '20 dogs',
            $this->translator()->transChoice('{n} dog and {c} cat|{n} dogs', 100, [
                'n' => 20,
                'c' => 3
            ])
        );

        $this->assertSame(
            '1 dog and 3 cat',
            $this->translator()->transChoice('{n} dog and {c} cat|{n} dogs', 1, [
                'c' => 3
            ])
        );

        $this->assertSame(
            '2,220 dogs',
            $this->translator()->transChoice('{n} dog|{n} dogs', 2, ['n' => number_format(2220)])
        );
    }

    public function testRussian(): void
    {
        $tr = $this->translator();
        $tr->setLocale('ru');

        $this->assertSame(
            '1 собака',
            $tr->transChoice('{n} dog|{n} dogs', 1)
        );

        $this->assertSame(
            '2 собаки',
            $tr->transChoice('{n} dog|{n} dogs', 2)
        );

        $this->assertSame(
            '8 собак',
            $tr->transChoice('{n} dog|{n} dogs', 8)
        );
    }

    public function testRussianFallback(): void
    {
        $tr = $this->translator();
        $tr->setLocale('ru');

        $this->assertSame(
            '1 собака',
            $tr->transChoice('{n} dog|{n} dogs', 1)
        );

        $this->assertSame(
            '1 cat',
            $tr->transChoice('{n} cat|{n} cats', 1)
        );

        $this->assertSame(
            '2 cats',
            $tr->transChoice('{n} cat|{n} cats', 2)
        );

        $this->assertSame(
            '8 cats',
            $tr->transChoice('{n} cat|{n} cats', 8)
        );
    }

    protected function translator(): Translator
    {
        $container = new Container();
        $container->bind(TranslatorConfig::class, new TranslatorConfig([
            'locale'  => 'en',
            'domains' => [
                'messages' => ['*']
            ]
        ]));

        $container->bindSingleton(TranslatorInterface::class, Translator::class);
        $container->bindSingleton(CatalogueManagerInterface::class, Catalogue\CatalogueManager::class);
        $container->bind(LoaderInterface::class, Catalogue\CatalogueLoader::class);

        $loader = new RuntimeLoader();
        $loader->addCatalogue('en', new Catalogue('en', [
            'messages' => [
                '{n} dog|{n} dogs' => '{n} dog|{n} dogs',
                '{n} cat|{n} cats' => '{n} cat|{n} cats',
            ]
        ]));

        $loader->addCatalogue('ru', new Catalogue('en', [
            'messages' => [
                '{n} dog|{n} dogs' => '{n} собака|{n} собаки|{n} собак',
            ]
        ]));


        $container->bind(LoaderInterface::class, $loader);

        return $container->get(TranslatorInterface::class);
    }
}
