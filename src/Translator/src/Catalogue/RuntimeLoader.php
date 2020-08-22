<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Exception\LocaleException;

final class RuntimeLoader implements LoaderInterface
{
    /**
     * @var CatalogueInterface[]
     */
    private $catalogues = [];

    /**
     * @inheritdoc
     */
    public function addCatalogue(string $locale, CatalogueInterface $catalogue): void
    {
        $this->catalogues[$locale] = $catalogue;
    }

    /**
     * @inheritdoc
     */
    public function hasLocale(string $locale): bool
    {
        return isset($this->catalogues[$locale]);
    }

    /**
     * @inheritdoc
     */
    public function getLocales(): array
    {
        return array_keys($this->catalogues);
    }

    /**
     * @inheritdoc
     */
    public function loadCatalogue(string $locale): CatalogueInterface
    {
        if (!$this->hasLocale($locale)) {
            throw new LocaleException($locale);
        }

        return $this->catalogues[$locale];
    }
}
