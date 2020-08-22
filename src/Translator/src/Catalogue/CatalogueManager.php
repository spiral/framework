<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var LoaderInterface */
    private $loader;

    /**
     * @internal
     * @var CacheInterface
     */
    private $cache = null;

    /** @var array */
    private $locales = [];

    /** @var Catalogue[] */
    private $catalogues = [];

    /**
     * @param LoaderInterface $loader
     * @param CacheInterface  $cache
     */
    public function __construct(LoaderInterface $loader, CacheInterface $cache = null)
    {
        $this->loader = $loader;
        $this->cache = $cache ?? new NullCache();
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function save(string $locale): void
    {
        $this->cache->saveLocale($locale, $this->get($locale)->getData());
    }

    /**
     * @inheritdoc
     */
    public function has(string $locale): bool
    {
        return isset($this->catalogues[$locale]) || in_array($locale, $this->getLocales());
    }

    /**
     * @inheritdoc
     */
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
