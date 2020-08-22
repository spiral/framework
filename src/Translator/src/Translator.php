<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Exception\LocaleException;
use Spiral\Translator\Exception\PluralizationException;
use Symfony\Component\Translation\IdentityTranslator;

/**
 * Implementation of Symfony\TranslatorInterface with memory caching, automatic message
 * registration and bundle/domain grouping.
 */
final class Translator implements TranslatorInterface, SingletonInterface
{
    /** @var TranslatorConfig */
    private $config;

    /** @var string */
    private $locale = '';

    /** @var IdentityTranslator @internal */
    private $identityTranslator;

    /** @var CatalogueManagerInterface */
    private $catalogueManager;

    /**
     * @param TranslatorConfig          $config
     * @param CatalogueManagerInterface $catalogueManager
     * @param IdentityTranslator        $identityTranslator
     */
    public function __construct(
        TranslatorConfig $config,
        CatalogueManagerInterface $catalogueManager,
        IdentityTranslator $identityTranslator = null
    ) {
        $this->config = $config;
        $this->identityTranslator = $identityTranslator ?? new IdentityTranslator();
        $this->catalogueManager = $catalogueManager;

        $this->locale = $this->config->getDefaultLocale();
        $this->catalogueManager->load($this->locale);
    }

    /**
     * @inheritdoc
     */
    public function getDomain(string $bundle): string
    {
        return $this->config->resolveDomain($bundle);
    }

    /**
     * @inheritdoc
     *
     * @throws LocaleException
     */
    public function setLocale(string $locale): void
    {
        if (!$this->catalogueManager->has($locale)) {
            throw new LocaleException($locale);
        }

        $this->locale = $locale;
        $this->catalogueManager->load($locale);
    }

    /**
     * @inheritdoc
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function getCatalogueManager(): CatalogueManagerInterface
    {
        return $this->catalogueManager;
    }

    /**
     * {@inheritdoc}
     *
     * Parameters will be embedded into string using { and } braces.
     *
     * @throws LocaleException
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->config->getDefaultDomain();
        $locale = $locale ?? $this->locale;

        $message = $this->get($locale, $domain, $id);

        return self::interpolate($message, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * Default symfony pluralizer to be used. Parameters will be embedded into string using { and }
     * braces. In addition you can use forced parameter {n} which contain formatted number value.
     *
     * @throws LocaleException
     * @throws PluralizationException
     */
    public function transChoice(
        $id,
        $number,
        array $parameters = [],
        $domain = null,
        $locale = null
    ) {
        $domain = $domain ?? $this->config->getDefaultDomain();
        $locale = $locale ?? $this->locale;

        try {
            $message = $this->get($locale, $domain, $id);

            $pluralized = $this->identityTranslator->trans(
                $message,
                ['%count%' => $number],
                null,
                $locale
            );
        } catch (\InvalidArgumentException $e) {
            //Wrapping into more explanatory exception
            throw new PluralizationException($e->getMessage(), $e->getCode(), $e);
        }

        if (empty($parameters['n']) && is_numeric($number)) {
            $parameters['n'] = $number;
        }

        return self::interpolate($pluralized, $parameters);
    }

    /**
     * Interpolate string with given parameters, used by many spiral components.
     *
     * Input: Hello {name}! Good {time}! + ['name' => 'Member', 'time' => 'day']
     * Output: Hello Member! Good Day!
     *
     * @param string $string
     * @param array  $values Arguments (key => value). Will skip unknown names.
     * @param string $prefix Placeholder prefix, "{" by default.
     * @param string $postfix Placeholder postfix, "}" by default.
     * @return string
     */
    public static function interpolate(
        string $string,
        array $values,
        string $prefix = '{',
        string $postfix = '}'
    ): string {
        $replaces = [];
        foreach ($values as $key => $value) {
            $value = (is_array($value) || $value instanceof \Closure) ? '' : $value;

            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    $value = $value->__toString();
                } else {
                    $value = '';
                }
            }

            $replaces[$prefix . $key . $postfix] = $value;
        }

        return strtr($string, $replaces);
    }

    /**
     * Check if string has translation braces [[ and ]].
     *
     * @param string $string
     * @return bool
     */
    public static function isMessage(string $string): bool
    {
        return substr($string, 0, 2) == self::I18N_PREFIX
            && substr($string, -2) == self::I18N_POSTFIX;
    }

    /**
     * Get translation message from the locale bundle or fallback to default locale.
     *
     * @param string $locale
     * @param string $domain
     * @param string $string
     * @return string
     */
    protected function get(string &$locale, string $domain, string $string): string
    {
        if ($this->catalogueManager->get($locale)->has($domain, $string)) {
            return $this->catalogueManager->get($locale)->get($domain, $string);
        }

        $locale = $this->config->getFallbackLocale();

        if ($this->catalogueManager->get($locale)->has($domain, $string)) {
            return $this->catalogueManager->get($locale)->get($domain, $string);
        }

        // we can automatically register message
        if ($this->config->isAutoRegisterMessages()) {
            $this->catalogueManager->get($locale)->set($domain, $string, $string);
            $this->catalogueManager->save($locale);
        }

        // Unable to find translation
        return $string;
    }
}
