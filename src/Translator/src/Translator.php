<?php

declare(strict_types=1);

namespace Spiral\Translator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Event\LocaleUpdated;
use Spiral\Translator\Exception\LocaleException;
use Spiral\Translator\Exception\PluralizationException;
use Symfony\Component\Translation\IdentityTranslator;

/**
 * Implementation of Symfony\TranslatorInterface with memory caching, automatic message
 * registration and bundle/domain grouping.
 */
#[Singleton]
final class Translator implements TranslatorInterface
{
    private string $locale;

    public function __construct(
        private readonly TranslatorConfig $config,
        private readonly CatalogueManagerInterface $catalogueManager,
        /** @internal */
        private readonly IdentityTranslator $identityTranslator = new IdentityTranslator(),
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
        $this->locale = $this->config->getDefaultLocale();
        $this->catalogueManager->load($this->locale);
    }

    public function getDomain(string $bundle): string
    {
        return $this->config->resolveDomain($bundle);
    }

    /**
     * @throws LocaleException
     */
    public function setLocale(string $locale): void
    {
        if (!$this->catalogueManager->has($locale)) {
            throw new LocaleException($locale);
        }

        $this->locale = $locale;
        $this->catalogueManager->load($locale);

        $this->dispatcher?->dispatch(new LocaleUpdated($locale));
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getCatalogueManager(): CatalogueManagerInterface
    {
        return $this->catalogueManager;
    }

    /**
     * Parameters will be embedded into string using { and } braces.
     *
     * @throws LocaleException
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $domain ??= $this->config->getDefaultDomain();
        $locale ??= $this->locale;

        $message = $this->get($locale, $domain, $id);

        return self::interpolate($message, $parameters);
    }

    /**
     * Default symfony pluralizer to be used. Parameters will be embedded into string using { and }
     * braces. In addition you can use forced parameter {n} which contain formatted number value.
     *
     * @throws LocaleException
     * @throws PluralizationException
     */
    public function transChoice(
        string $id,
        string|int $number,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ): string {
        $domain ??= $this->config->getDefaultDomain();
        $locale ??= $this->locale;

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
     * @param array  $values Arguments (key => value). Will skip unknown names.
     * @param string $prefix Placeholder prefix, "{" by default.
     * @param string $postfix Placeholder postfix, "}" by default.
     */
    public static function interpolate(
        string $string,
        array $values,
        string $prefix = '{',
        string $postfix = '}'
    ): string {
        $replaces = [];
        foreach ($values as $key => $value) {
            $value = (\is_array($value) || $value instanceof \Closure) ? '' : $value;

            if (\is_object($value)) {
                if (\method_exists($value, '__toString')) {
                    $value = $value->__toString();
                } else {
                    $value = '';
                }
            }

            $replaces[$prefix . $key . $postfix] = $value;
        }

        return \strtr($string, $replaces);
    }

    /**
     * Check if string has translation braces [[ and ]].
     */
    public static function isMessage(string $string): bool
    {
        return \substr($string, 0, 2) == self::I18N_PREFIX
            && \substr($string, -2) == self::I18N_POSTFIX;
    }

    /**
     * Get translation message from the locale bundle or fallback to default locale.
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
