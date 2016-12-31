<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator;

use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\MemoryInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Exceptions\LocaleException;
use Spiral\Translator\Exceptions\PluralizationException;
use Symfony\Component\Translation\MessageSelector;

/**
 * Simple implementation of Symfony\TranslatorInterface with memory caching and automatic message
 * registration.
 */
class Translator extends Component implements SingletonInterface, TranslatorInterface
{
    use BenchmarkTrait;

    /**
     * Memory section.
     */
    const MEMORY = 'translator';

    /**
     * @var TranslatorConfig
     */
    private $config = null;

    /**
     * Symfony selection logic is little
     *
     * @var MessageSelector
     */
    private $selector = null;

    /**
     * Current locale.
     *
     * @var string
     */
    private $locale = '';

    /**
     * Loaded catalogues (hash).
     *
     * @var Catalogue[]
     */
    private $catalogues = [];

    /**
     * @var array
     */
    private $loadedLocales = [];

    /**
     * Catalogue to be used for fallback translation.
     *
     * @var Catalogue|null
     */
    private $fallback = null;

    /**
     * To load locale data from application files.
     *
     * @var LocatorInterface
     */
    protected $source = null;

    /**
     * @var MemoryInterface
     */
    protected $memory = null;

    /**
     * @param TranslatorConfig $config
     * @param MemoryInterface  $memory
     * @param LocatorInterface $source
     * @param MessageSelector  $selector
     */
    public function __construct(
        TranslatorConfig $config,
        MemoryInterface $memory,
        LocatorInterface $source,
        MessageSelector $selector = null
    ) {
        $this->config = $config;
        $this->memory = $memory;
        $this->source = $source;
        $this->selector = $selector;

        $this->locale = $this->config->defaultLocale();

        //List of known and loaded locales (loading can be delayed)
        $this->loadedLocales = (array)$this->memory->loadData(static::MEMORY);
    }

    /**
     * @return LocatorInterface
     */
    public function getSource(): LocatorInterface
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveDomain(string $bundle): string
    {
        return $this->config->resolveDomain($bundle);
    }

    /**
     * Create copy of translator with different locale. Catalogues and
     * sources are not copied, cache synced.
     *
     * @param string $locale
     *
     * @return Translator
     */
    public function withLocale(string $locale): Translator
    {
        $translator = clone $this;
        $translator->setLocale($locale);

        //Keep direct reference, check if needed
        $translator->catalogues = &$this->catalogues;
        $translator->loadedLocales = &$this->loadedLocales;

        return $translator;
    }

    /**
     * {@inheritdoc}
     *
     * Non immutable version of withLocale.
     *
     * @return $this
     *
     * @throws LocaleException
     */
    public function setLocale($locale)
    {
        if (!$this->hasLocale($locale)) {
            throw new LocaleException($locale);
        }

        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
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
        $domain = $domain ?? $this->config->defaultDomain();
        $locale = $locale ?? $this->locale;

        //Automatically falls back to default locale
        $translation = $this->get($domain, $id, $locale);

        return \Spiral\interpolate($translation, $parameters);
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
        $domain = $domain ?? $this->config->defaultDomain();
        $locale = $locale ?? $this->locale;

        if (empty($parameters['{n}'])) {
            $parameters['{n}'] = number_format($number);
        }

        //Automatically falls back to default locale
        $translation = $this->get($domain, $id, $locale);

        try {
            $pluralized = $this->selector->choose($translation, $number, $locale);
        } catch (\InvalidArgumentException $e) {
            //Wrapping into more explanatory exception
            throw new PluralizationException($e->getMessage(), $e->getCode(), $e);
        }

        return \Spiral\interpolate($pluralized, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): array
    {
        if (!empty($this->loadedLocales)) {
            return array_keys($this->loadedLocales);
        }

        return $this->source->getLocales();
    }

    /**
     * Return catalogue for specific locate or return default one if no locale specified.
     *
     * @param string $locale
     *
     * @return Catalogue
     *
     * @throws LocaleException
     */
    public function getCatalogue(string $locale = null): Catalogue
    {
        if (empty($locale)) {
            $locale = $this->locale;
        }

        if (!$this->hasLocale($locale)) {
            throw new LocaleException("Undefined locale '{$locale}'");
        }

        if (!isset($this->catalogues[$locale])) {
            $this->catalogues[$locale] = $this->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }

    /**
     * Load all possible locales into memory.
     *
     * @return self
     */
    public function loadLocales(): Translator
    {
        foreach ($this->source->getLocales() as $locale) {
            $this->loadCatalogue($locale);
        }

        return $this;
    }

    /**
     * Flush all loaded locales data.
     *
     * @return self
     */
    public function flushLocales(): Translator
    {
        $this->loadedLocales = [];
        $this->catalogues = [];

        $this->memory->saveData(static::MEMORY, []);

        //Flushing fallback catalogue
        $this->fallback = null;

        return $this;
    }

    /**
     * Get message from specific locale, add it into fallback locale cache (to be later exported) if
     * enabled (see TranslatorConfig) and no translations found.
     *
     * @param string $domain
     * @param string $string
     * @param string $locale
     *
     * @return string
     */
    protected function get(string $domain, string $string, string $locale): string
    {
        //Active language first
        if ($this->getCatalogue($locale)->has($domain, $string)) {
            return $this->getCatalogue($locale)->get($domain, $string);
        }

        $fallback = $this->fallbackCatalogue();

        if ($fallback->has($domain, $string)) {
            return $fallback->get($domain, $string);
        }

        //Automatic message registration.
        if ($this->config->registerMessages()) {
            $fallback->set($domain, $string, $string);
            $fallback->saveDomains();
        }

        //Unable to find translation
        return $string;
    }

    /**
     * @return Catalogue
     */
    protected function fallbackCatalogue(): Catalogue
    {
        if (empty($this->fallback)) {
            $this->fallback = $this->loadCatalogue($this->config->fallbackLocale());
        }

        return $this->fallback;
    }

    /**
     * Load catalogue data from source.
     *
     * @param string $locale
     *
     * @return Catalogue
     */
    protected function loadCatalogue(string $locale): Catalogue
    {
        $catalogue = new Catalogue($locale, $this->memory);

        if (array_key_exists($locale, $this->loadedLocales) && $this->config->cacheLocales()) {

            //Has been loaded
            $catalogue->loadDomains($this->loadedLocales[$locale]);

            return $catalogue;
        }

        $benchmark = $this->benchmark('load', $locale);
        try {

            //Loading catalogue data from source
            foreach ($this->source->loadLocale($locale) as $messageCatalogue) {
                $catalogue->mergeFrom($messageCatalogue);
            }

            //To remember that locale already loaded
            $this->loadedLocales[$locale] = $catalogue->loadedDomains();
            $this->memory->saveData(static::MEMORY, $this->loadedLocales);

            //Saving domains memory
            $catalogue->saveDomains();
        } finally {
            $this->benchmark($benchmark);
        }

        return $catalogue;
    }

    /**
     * Check if given locale exists.
     *
     * @param string $locale
     *
     * @return bool
     */
    private function hasLocale(string $locale): bool
    {
        if (array_key_exists($locale, $this->loadedLocales)) {
            return true;
        }

        return $this->source->hasLocale($locale);
    }

    /**
     * Check if string has translation braces [[ and ]].
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isMessage(string $string): bool
    {
        return substr($string, 0, 2) == self::I18N_PREFIX
            && substr($string, -2) == self::I18N_POSTFIX;
    }
}