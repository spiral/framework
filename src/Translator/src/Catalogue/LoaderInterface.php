<?php

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

use Spiral\Translator\CatalogueInterface;

interface LoaderInterface
{
    /**
     * Check if locale data exists.
     */
    public function hasLocale(string $locale): bool;

    /**
     * List of all known locales.
     */
    public function getLocales(): array;

    public function loadCatalogue(string $locale): CatalogueInterface;
}
