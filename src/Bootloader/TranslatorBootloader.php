<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;
use Symfony\Component\Translation\Dumper;
use Symfony\Component\Translation\Loader;

class TranslatorBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        TranslatorInterface::class => Translator::class,
        CataloguesInterface::class => CatalogueManager::class,
    ];

    const BINDINGS = [
        LoaderInterface::class => CatalogueLoader::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param DirectoriesInterface  $directories
     * @param EnvironmentInterface  $environment
     */
    public function boot(
        ConfiguratorInterface $configurator,
        DirectoriesInterface $directories,
        EnvironmentInterface $environment
    ) {
        $configurator->setDefaults('translator', [
            'locale'         => $environment->get('LOCALE', 'en'),
            'fallbackLocale' => $environment->get('LOCALE', 'en'),
            'directory'      => $directories->get('app') . '/locales/',
            'autoRegister'   => $environment->get('DEBUG', true),
            'loaders'        => [
                'php'  => Loader\PhpFileLoader::class,
                'po'   => Loader\PoFileLoader::class,
                'csv'  => Loader\CsvFileLoader::class,
                'json' => Loader\JsonFileLoader::class
            ],
            'dumpers'        => [
                'php'  => Dumper\PhpFileDumper::class,
                'po'   => Dumper\PoFileDumper::class,
                'csv'  => Dumper\CsvFileDumper::class,
                'json' => Dumper\JsonFileDumper::class,
            ],
            'domains'        => [
                // by default we can store all messages in one domain
                'messages' => ['*']
            ]
        ]);
    }
}