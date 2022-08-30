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
     */
    public function setLocales(?array $locales);

    /**
     * Get cached list of locales.
     */
    public function getLocales(): ?array;

    /**
     * Store locale data.
     */
    public function saveLocale(string $locale, ?array $data);

    /**
     * Load cached locale data.
     */
    public function loadLocale(string $locale): ?array;
}
