<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Configs;

use Spiral\Core\InjectableConfig;
use Spiral\Support\Patternizer;

/**
 * Translation component configuration.
 */
class TranslatorConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'translator';

    /**
     * @var Patternizer
     */
    private $patternizer = null;

    /**
     * @var array
     */
    protected $config = [
        'locale'           => '',
        'fallbackLocale'   => '',
        'localesDirectory' => '',
        'cacheLocales'     => true,
        'autoRegister'     => true,
        'domains'          => [],
        'loaders'          => [],
        'dumpers'          => []
    ];

    /**
     * Default transation domain.
     *
     * @return string
     */
    public function defaultDomain(): string
    {
        return 'messages';
    }

    /**
     * @param Patternizer $patternizer
     *
     * @return self
     */
    public function withPatternizer(Patternizer $patternizer): TranslatorConfig
    {
        $config = clone $this;
        $config->patternizer = $patternizer;

        return $config;
    }

    /**
     * @return string
     */
    public function defaultLocale(): string
    {
        return $this->config['locale'];
    }

    /**
     * @return string
     */
    public function fallbackLocale(): string
    {
        return $this->config['fallbackLocale'];
    }

    /**
     * @return bool
     */
    public function cacheLocales(): bool
    {
        if (array_key_exists('cacheLocales', $this->config)) {
            return $this->config['cacheLocales'];
        }

        //Legacy configs
        return empty($this->config['autoReload']);
    }

    /**
     * @return bool
     */
    public function registerMessages(): bool
    {
        return !empty($this->config['autoRegister']) || !empty($this->config['registerMessages']);
    }

    /**
     * @return string
     */
    public function localesDirectory(): string
    {
        return $this->config['localesDirectory'];
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function localeDirectory(string $locale): string
    {
        return $this->config['localesDirectory'] . $locale . '/';
    }

    /**
     * Get domain name associated with given bundle.
     *
     * @param string $bundle
     *
     * @return string
     */
    public function resolveDomain(string $bundle): string
    {
        $this->patternizer = $this->patternizer ?? new Patternizer();

        $bundle = strtolower(str_replace(['/', '\\'], '-', $bundle));

        foreach ($this->config['domains'] as $domain => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->patternizer->matches($bundle, $pattern)) {
                    return $domain;
                }
            }
        }

        //We can use bundle itself as domain
        return $bundle;
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    public function hasLoader(string $extension): bool
    {
        return isset($this->config['loaders'][$extension]);
    }

    /**
     * @param string $extension
     *
     * @return string
     */
    public function loaderClass(string $extension): string
    {
        return $this->config['loaders'][$extension];
    }

    /**
     * @param string $dumper
     *
     * @return bool
     */
    public function hasDumper(string $dumper): bool
    {
        return isset($this->config['dumpers'][$dumper]);
    }

    /**
     * @param string $dumper
     *
     * @return string
     */
    public function dumperClass(string $dumper): string
    {
        return $this->config['dumpers'][$dumper];
    }
}