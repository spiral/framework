<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Translator;

use Doctrine\Common\Inflector\Inflector;
use Psr\Log\LoggerAwareInterface;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\HippocampusInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
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
    /**
     * Catalogue loading.
     */
    use BenchmarkTrait;

    /**
     * Declares to Spiral IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Memory section.
     */
    const MEMORY = 'translator';

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
     * Loaded catalogues.
     *
     * @var Catalogue
     */
    private $catalogues = [];

    /**
     * Catalogue to be used for fallback translation.
     *
     * @var Catalogue
     */
    private $fallbackCatalogue = null;

    /**
     * @var array
     */
    private $loadedLocales = [];

    /**
     * @var TranslatorConfig
     */
    protected $config = null;

    /**
     * @var array
     */
    protected $domains = [];

    /**
     * To load locale data from application files.
     *
     * @var FilesInterface
     */
    protected $source = null;

    /**
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @param TranslatorConfig     $config
     * @param HippocampusInterface $memory
     * @param SourceInterface      $source
     * @param MessageSelector      $selector
     */
    public function __construct(
        TranslatorConfig $config,
        HippocampusInterface $memory,
        SourceInterface $source,
        MessageSelector $selector = null
    ) {
        $this->config = $config;
        $this->memory = $memory;
        $this->source = $source;
        $this->selector = $selector;

        //List of known and loaded locales
        $this->loadedLocales = (array)$this->memory->loadData(static::MEMORY);

        $this->locale = $this->config->defaultLocale();
        $this->fallbackCatalogue = $this->loadCatalogue($this->config->fallbackLocale());
    }

    /**
     * @return SourceInterface
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveDomain($bundle)
    {
        return $this->config->resolveDomain($bundle);
    }

    /**
     * {@inheritdoc}
     *
     * Parameters will be embedded into string using { and } braces.
     *
     * @throws LocaleException
     */
    public function trans(
        $id,
        array $parameters = [],
        $domain = self::DEFAULT_DOMAIN,
        $locale = null
    ) {
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
     */
    public function transChoice(
        $id,
        $number,
        array $parameters = [],
        $domain = self::DEFAULT_DOMAIN,
        $locale = null
    ) {
        if (empty($parameters['{n}'])) {
            $parameters['{n}'] = number_format($number);
        }

        //Automatically falls back to default locale
        $translation = $this->get($domain, $id, $locale);

        try {
            $pluralized = $this->selector->choose($translation, $number, $locale);
        } catch (\InvalidArgumentException $exception) {
            //Wrapping into more explanatory exception
            throw new PluralizationException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return \Spiral\interpolate($pluralized, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws LocaleException
     */
    public function setLocale($locale)
    {
        if (!$this->hasLocale($locale)) {
            throw new LocaleException("Undefined locale '{$locale}'.");
        }

        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     *
     * Attention, method will return cached locales first.
     */
    public function getLocales()
    {
        if (!empty($this->loadedLocales)) {
            return array_keys($this->loadedLocales);
        }

        $this->loadLocales();

        return $this->source->getLocales();
    }

    /**
     * Return catalogue for specific locate or return default one if no locale specified.
     *
     * @param string $locale
     * @return Catalogue
     */
    public function getCatalogue($locale = null)
    {
        if (empty($locale)) {
            $locale = $this->locale;
        }

        if (!$this->hasLocale($locale)) {
            throw new LocaleException("Undefined locale '{$locale}'.");
        }

        if (!isset($this->catalogues[$locale])) {
            $this->catalogues[$locale] = $this->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }

    /**
     * Flush all loaded locales data.
     *
     * @return $this
     */
    public function flushLocales()
    {
        $this->loadedLocales = [];
        $this->catalogues = [];
        $this->memory->saveData(static::MEMORY, []);

        //Reloading fallback locale
        $this->fallbackCatalogue = $this->loadCatalogue($this->config->fallbackLocale());

        return $this;
    }

    /**
     * Load all possible locales.
     *
     * @return $this
     */
    public function loadLocales()
    {
        foreach ($this->source->getLocales() as $locale) {
            $this->loadCatalogue($locale);
        }

        return $this;
    }

    /**
     * Get message from specific locale, add it into fallback locale cache (to be later exported) if
     * enabled (see TranslatorConfig) and no translations found.
     *
     * @param string $domain
     * @param string $string
     * @param string $locale
     * @return string
     */
    protected function get($domain, $string, $locale)
    {
        if ($this->getCatalogue($locale)->has($domain, $string)) {
            return $this->getCatalogue($locale)->get($domain, $string);
        } elseif ($this->fallbackCatalogue->has($domain, $string)) {
            return $this->fallbackCatalogue->get($domain, $string);
        }

        if ($this->config->autoRegistration()) {
            /*
             * Automatic message registration.
             */
            $this->fallbackCatalogue->set($domain, $string, $string);

            //Into memory
            $this->fallbackCatalogue->saveDomains();
        }

        //Unable to find translation
        return $string;
    }

    /**
     * Load catalogue data from source.
     *
     * @param string $locale
     * @return Catalogue
     */
    protected function loadCatalogue($locale)
    {
        $catalogue = new Catalogue($locale, $this->memory);

        if (array_key_exists($locale, $this->loadedLocales) && !$this->config->autoReload()) {
            //Has been loaded
            return $catalogue;
        }

        $benchmark = $this->benchmark('load', $locale);
        try {
            //Loading catalogue data from source
            foreach ($this->source->loadLocale($locale) as $messageCatalogue) {
                $catalogue->mergeFrom($messageCatalogue);
            }

            //To remember that locale already loaded
            $this->loadedLocales[$locale] = $catalogue->getDomains();
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
     * @return bool
     */
    private function hasLocale($locale)
    {
        if (array_key_exists($locale, $this->loadedLocales)) {
            return true;
        }

        return $this->source->hasLocale($locale);
    }
}