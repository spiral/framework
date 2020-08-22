<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

interface CacheInterface
{
    /**
     * Cache list of available locates.
     *
     * @param array|null $locales
     */
    public function setLocales(?array $locales);

    /**
     * Get cached list of locales.
     *
     * @return array|null
     */
    public function getLocales(): ?array;

    /**
     * Store locale data.
     *
     * @param string     $locale
     * @param array|null $data
     */
    public function saveLocale(string $locale, ?array $data);

    /**
     * Load cached locale data.
     *
     * @param string $locale
     * @return array|null
     */
    public function loadLocale(string $locale): ?array;
}
