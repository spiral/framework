<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Translator\Catalogue\CatalogueLoader;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\Catalogue\LoaderInterface;
use Spiral\Translator\CataloguesInterface;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

class TranslatorBootloader extends Bootloader
{
    const SINGLETONS = [
        TranslatorInterface::class => Translator::class,
        CataloguesInterface::class => CatalogueManager::class,
    ];

    const BINDINGS = [
        LoaderInterface::class => CatalogueLoader::class
    ];
}