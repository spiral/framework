<?php

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Translator\Exception\LocaleException;

/**
 * Manages list of locales and associated catalogues.
 */
interface CatalogueManagerInterface
{
    /**
     * Get list of all existed locales.
     */
    public function getLocales(): array;

    /**
     * Load catalogue.
     *
     * @throws LocaleException
     */
    public function load(string $locale): CatalogueInterface;

    /**
     * Save catalogue changes.
     */
    public function save(string $locale): void;

    /**
     * Check if locale exists.
     */
    public function has(string $locale): bool;

    /**
     * Get catalogue associated with the locale.
     *
     * @throws LocaleException
     */
    public function get(string $locale): CatalogueInterface;
}
