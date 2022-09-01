<?php

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

use Spiral\Translator\Catalogue;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\CatalogueManagerInterface;

/**
 * Manages catalogues and their cached data.
 */
final class CatalogueManager implements CatalogueManagerInterface
{
    private array $locales = [];

    /** @internal */
    private readonly CacheInterface $cache;

    /** @var CatalogueInterface[] */
    private array $catalogues = [];

    public function __construct(
        private readonly LoaderInterface $loader,
        CacheInterface $cache = null
    ) {
        $this->cache = $cache ?? new NullCache();
    }

    public function getLocales(): array
    {
        if ($this->locales !== []) {
            return $this->locales;
        }

        $this->locales = (array)$this->cache->getLocales();
        if ($this->locales === []) {
            $this->locales = $this->loader->getLocales();
            $this->cache->setLocales($this->locales);
        }

        return $this->locales;
    }

    public function load(string $locale): CatalogueInterface
    {
        if (isset($this->catalogues[$locale])) {
            return $this->catalogues[$locale];
        }

        $data = (array)$this->cache->loadLocale($locale);
        if (!empty($data)) {
            $this->catalogues[$locale] = new Catalogue($locale, $data);
        } else {
            $this->catalogues[$locale] = $this->loader->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }

    public function save(string $locale): void
    {
        $this->cache->saveLocale($locale, $this->get($locale)->getData());
    }

    public function has(string $locale): bool
    {
        return isset($this->catalogues[$locale]) || \in_array($locale, $this->getLocales());
    }

    public function get(string $locale): CatalogueInterface
    {
        return $this->load($locale);
    }

    /**
     * Reset all cached data and loaded locates.
     */
    public function reset(): void
    {
        $this->cache->setLocales(null);
        foreach ($this->getLocales() as $locale) {
            $this->cache->saveLocale($locale, null);
        }

        $this->locales = [];
        $this->catalogues = [];
    }
}
