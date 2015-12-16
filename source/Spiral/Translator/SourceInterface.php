<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */
namespace Spiral\Translator;

use Spiral\Translator\Exceptions\SourceException;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Translation source interface, responsible for feeding translation with domains data.
 */
interface SourceInterface
{
    /**
     * Check if given locale known to source.
     *
     * @param string $locale
     * @return bool
     */
    public function hasLocale($locale);

    /**
     * Load and return all locale messages aggregated by their domain.
     *
     * @param string $locale
     * @return MessageCatalogue[]
     * @throws SourceException
     */
    public function loadLocale($locale);

    /**
     * List of available locales. Can be rewritten with other logic in future.
     *
     * @return array
     */
    public function getLocales();
}