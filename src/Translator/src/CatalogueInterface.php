<?php

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Translator\Exception\CatalogueException;

interface CatalogueInterface
{
    public function getLocale(): string;

    /**
     * All domains registered within catalogue.
     */
    public function getDomains(): array;

    /**
     * Check if domain message exists.
     */
    public function has(string $domain, string $id): bool;

    /**
     * Get message from the catalogue.
     *
     * @throws CatalogueException
     */
    public function get(string $domain, string $id): string;

    /**
     * Set/replace translation in catalogue.
     */
    public function set(string $domain, string $id, string $translation);

    /**
     * Must return all locale data.
     */
    public function getData(): array;
}
