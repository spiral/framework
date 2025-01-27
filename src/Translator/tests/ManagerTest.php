<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Translator\Catalogue\CacheInterface;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class ManagerTest extends TestCase
{
    public function testLocalesFromLoader(): void
    {
        $cache = m::mock(CacheInterface::class);
        $cache->shouldReceive('getLocales')->andReturn(null);
        $cache->shouldReceive('setLocales')->andReturn(null);

        $manager = new CatalogueManager(new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ])), $cache);

        self::assertTrue($manager->has('ru'));
        self::assertTrue($manager->has('en'));
    }

    public function testLocalesFromMemory(): void
    {
        $cache = m::mock(CacheInterface::class);
        $cache->shouldReceive('getLocales')->andReturn(['en', 'ru']);
        $cache->shouldNotReceive('setLocales')->andReturn(null);

        $manager = new CatalogueManager(new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ])), $cache);

        self::assertTrue($manager->has('ru'));
        self::assertTrue($manager->has('en'));
    }

    public function testCatalogue(): void
    {
        $cache = m::mock(CacheInterface::class);
        $cache->shouldReceive('getLocales')->andReturn(['en', 'ru']);

        $manager = new CatalogueManager(new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ])), $cache);

        $cache->shouldReceive('loadLocale')->with('ru')->andReturn([]);

        $catalogue = $manager->get('ru');
        self::assertInstanceOf(CatalogueInterface::class, $catalogue);

        self::assertTrue($catalogue->has('messages', 'message'));
        self::assertSame('translation', $catalogue->get('messages', 'message'));

        $cache->shouldReceive('saveLocale')->with(
            'ru',
            [
                'messages' => [
                    'message' => 'translation',
                    'should_be_override' => 'changed by application translation',
                ],
                'views'    => [
                    'Welcome To Spiral' => 'Добро пожаловать в Spiral Framework',
                    'Twig Version'      => 'Twig версия',
                ],
            ],
        )->andReturn(null);

        $cache->shouldReceive('saveLocale')->with(
            'ru',
            [
                'messages' => [
                    'message' => 'new message',
                    'should_be_override' => 'changed by application translation',
                ],
                'views'    => [
                    'Welcome To Spiral' => 'Добро пожаловать в Spiral Framework',
                    'Twig Version'      => 'Twig версия',
                ],
            ],
        )->andReturn(null);

        $catalogue->set('messages', 'message', 'new message');
        $manager->save('ru');
    }

    public function testFromMemory(): void
    {
        $cache = m::mock(CacheInterface::class);
        $cache->shouldReceive('getLocales')->andReturn(['en', 'ru']);

        $cache->shouldReceive('loadLocale')->with(
            'ru',
        )->andReturn([
            'messages' => [
                'message' => 'new message',
            ],
            'views'    => [
                'Welcome To Spiral' => 'Добро пожаловать в Spiral Framework',
                'Twig Version'      => 'Twig версия',
            ],
        ]);

        $manager = new CatalogueManager(new CatalogueLoader(new TranslatorConfig([
            'directory' => __DIR__ . '/fixtures/locales/',
            'loaders'   => [
                'php' => PhpFileLoader::class,
                'po'  => PoFileLoader::class,
            ],
        ])), $cache);

        $cache->shouldReceive('loadLocale')->with('ru')->andReturn([]);

        $catalogue = $manager->get('ru');
        self::assertInstanceOf(CatalogueInterface::class, $catalogue);

        self::assertTrue($catalogue->has('messages', 'message'));
        self::assertSame('new message', $catalogue->get('messages', 'message'));

        $cache->shouldReceive('setLocales')->with(null);
        $cache->shouldReceive('saveLocale')->with('ru', null);
        $cache->shouldReceive('saveLocale')->with('en', null);

        $manager->reset();
    }
}
