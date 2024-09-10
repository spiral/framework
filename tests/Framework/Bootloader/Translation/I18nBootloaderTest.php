<?php

declare(strict_types=1);

namespace Framework\Bootloader\Translation;

use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\CsvFileLoader;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Dumper\PhpFileDumper;
use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Translation\Dumper\CsvFileDumper;
use Symfony\Component\Translation\Dumper\JsonFileDumper;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment\DebugMode;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\I18nBootloader;
use Spiral\Tests\Framework\BaseTestCase;
use Spiral\Translator\Catalogue\CacheInterface;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\MemoryCache;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\Loader;

class I18nBootloaderTest extends BaseTestCase
{
    public function testTranslatorInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            \Symfony\Contracts\Translation\TranslatorInterface::class,
            Translator::class
        );

        $this->assertContainerBoundAsSingleton(TranslatorInterface::class, Translator::class);
    }

    public function testCatalogueManagerInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            CatalogueManagerInterface::class,
            CatalogueManager::class
        );
    }

    public function testLoaderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            LoaderInterface::class,
            CatalogueLoader::class
        );
    }

    public function testCacheInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            CacheInterface::class,
            MemoryCache::class
        );
    }

    public function testIdentityTranslatorBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            IdentityTranslator::class,
            IdentityTranslator::class
        );
    }

    public function testAddDirectory(): void
    {
        $this->getContainer()->get(I18nBootloader::class)->addDirectory('directory');

        $this->assertSame(
            [
                'directory',
            ],
            $this->getConfig(TranslatorConfig::CONFIG)['directories']
        );
    }

    public function testDefaultConfig(): void
    {
        $env = $this->getContainer()->get(EnvironmentInterface::class);
        $dirs = $this->getContainer()->get(DirectoriesInterface::class);
        $debugMode = $this->getContainer()->get(DebugMode::class);

        $this->assertSame(
            [
                'locale' => $env->get('LOCALE', 'en'),
                'fallbackLocale' => $env->get('LOCALE', 'en'),
                'directory' => $dirs->get('locale'),
                'directories' => [],
                'autoRegister' => $debugMode->isEnabled(),
                'loaders' => [
                    'php' => PhpFileLoader::class,
                    'po' => PoFileLoader::class,
                    'csv' => CsvFileLoader::class,
                    'json' => JsonFileLoader::class,
                ],
                'dumpers' => [
                    'php' => PhpFileDumper::class,
                    'po' => PoFileDumper::class,
                    'csv' => CsvFileDumper::class,
                    'json' => JsonFileDumper::class,
                ],
                'domains' => [
                    'messages' => ['*'],
                ],
            ],
            $this->getConfig(TranslatorConfig::CONFIG)
        );
    }
}
