<?php

declare(strict_types=1);

namespace Framework\Bootloader\Translation;

use Spiral\Tests\Framework\BaseTest;
use Spiral\Translator\Catalogue\CacheInterface;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\MemoryCache;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\IdentityTranslator;

class I18nBootloaderTest extends BaseTest
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
}
