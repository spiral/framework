<?php

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Exception\LocaleException;

final class RuntimeLoader implements LoaderInterface
{
    /**
     * @var CatalogueInterface[]
     */
    private array $catalogues = [];

    public function addCatalogue(string $locale, CatalogueInterface $catalogue): void
    {
        $this->catalogues[$locale] = $catalogue;
    }

    public function hasLocale(string $locale): bool
    {
        return isset($this->catalogues[$locale]);
    }

    public function getLocales(): array
    {
        return \array_keys($this->catalogues);
    }

    public function loadCatalogue(string $locale): CatalogueInterface
    {
        if (!$this->hasLocale($locale)) {
            throw new LocaleException($locale);
        }

        return $this->catalogues[$locale];
    }
}
