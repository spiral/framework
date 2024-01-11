<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment\DebugMode;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Singleton;
use Spiral\Translator\Catalogue\CacheInterface;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CatalogueManagerInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\MemoryCache;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader;

/**
 * Attention, the default language would not be automatically reset in finalizers. Make sure to properly design your
 * middleware.
 */
#[Singleton]
final class I18nBootloader extends Bootloader
{
    protected const SINGLETONS = [
        \Symfony\Contracts\Translation\TranslatorInterface::class => TranslatorInterface::class,
        TranslatorInterface::class => Translator::class,
        CatalogueManagerInterface::class => CatalogueManager::class,
        LoaderInterface::class => CatalogueLoader::class,
        CacheInterface::class => MemoryCache::class,
        IdentityTranslator::class => [self::class, 'identityTranslator'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(EnvironmentInterface $env, DirectoriesInterface $dirs, DebugMode $debugMode): void
    {
        if (!$dirs->has('locale')) {
            $dirs->set('locale', $dirs->get('app') . 'locale/');
        }

        $this->config->setDefaults(
            TranslatorConfig::CONFIG,
            [
                'locale' => $env->get('LOCALE', 'en'),
                'fallbackLocale' => $env->get('LOCALE', 'en'),
                'directory' => $dirs->get('locale'),
                'directories' => [],
                'autoRegister' => $debugMode->isEnabled(),
                'loaders' => [
                    'php' => Loader\PhpFileLoader::class,
                    'po' => Loader\PoFileLoader::class,
                    'csv' => Loader\CsvFileLoader::class,
                    'json' => Loader\JsonFileLoader::class,
                ],
                'dumpers' => [
                    'php' => Dumper\PhpFileDumper::class,
                    'po' => Dumper\PoFileDumper::class,
                    'csv' => Dumper\CsvFileDumper::class,
                    'json' => Dumper\JsonFileDumper::class,
                ],
                'domains' => [
                    // by default we can store all messages in one domain
                    'messages' => ['*'],
                ],
            ]
        );
    }

    /**
     * @param non-empty-string $directory
     */
    public function addDirectory(string $directory): void
    {
        $this->config->modify(TranslatorConfig::CONFIG, new Append('directories', null, $directory));
    }

    private function identityTranslator(): IdentityTranslator
    {
        return new IdentityTranslator();
    }
}
