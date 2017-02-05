<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator;

/**
 * Spiral translation built at top of symfony Translator and provides ability to route multiple
 * string sets (bundles) into one bigger domain. Such technique provides ability to collect and
 * generate location files based on application source without extra code.
 */
interface TranslatorInterface extends \Symfony\Component\Translation\TranslatorInterface
{
    /**
     * Default set of braces to be used in classes or views for indication of translatable content.
     */
    const I18N_PREFIX  = '[[';
    const I18N_POSTFIX = ']]';

    /**
     * Resolve domain name for given bundle.
     *
     * @param string $bundle
     *
     * @return string
     */
    public function resolveDomain(string $bundle): string;

    /**
     * Get list of supported locales.
     *
     * @return array
     */
    public function getLocales(): array;
}
