<?php

declare(strict_types=1);

namespace Spiral\Translator\Config;

use Spiral\Core\InjectableConfig;
use Spiral\Translator\Matcher;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;

final class TranslatorConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    public const CONFIG = 'translator';

    /**
     * @var array{
     *     locale: string,
     *     fallbackLocale?: string,
     *     directory: string,
     *     cacheLocales: bool,
     *     autoRegister: bool,
     *     domains: array<non-empty-string, array<string>>,
     *     loaders: class-string<LoaderInterface>[],
     *     dumpers: class-string<DumperInterface>[]
     * }
     */
    protected array $config = [
        'locale'         => '',
        'directory'      => '',
        'cacheLocales'   => true,
        'autoRegister'   => true,
        'domains'        => [],
        'loaders'        => [],
        'dumpers'        => [],
    ];

    private readonly Matcher $matcher;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->matcher = new Matcher();
    }

    /**
     * Default translation domain.
     */
    public function getDefaultDomain(): string
    {
        return 'messages';
    }

    public function getDefaultLocale(): string
    {
        return $this->config['locale'] ?? '';
    }

    public function getFallbackLocale(): string
    {
        return $this->config['fallbackLocale'] ?? $this->getDefaultLocale();
    }

    public function isAutoRegisterMessages(): bool
    {
        return !empty($this->config['autoRegister']) || !empty($this->config['registerMessages']);
    }

    public function getLocalesDirectory(): string
    {
        return $this->config['localesDirectory'] ?? $this->config['directory'] ?? '';
    }

    public function getLocaleDirectory(string $locale): string
    {
        return $this->getLocalesDirectory() . $locale . '/';
    }

    /**
     * Get domain name associated with given bundle.
     */
    public function resolveDomain(string $bundle): string
    {
        $bundle = \strtolower(\str_replace(['/', '\\'], '-', $bundle));
        $domains = (array) ($this->config['domains'] ?? []);

        foreach ($domains as $domain => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->matcher->matches($bundle, $pattern)) {
                    return $domain;
                }
            }
        }

        //We can use bundle itself as domain
        return $bundle;
    }

    public function hasLoader(string $extension): bool
    {
        return isset($this->config['loaders'][$extension]);
    }

    public function getLoader(string $extension): LoaderInterface
    {
        $class = $this->config['loaders'][$extension];

        return new $class();
    }

    public function hasDumper(string $dumper): bool
    {
        return isset($this->config['dumpers'][$dumper]);
    }

    public function getDumper(string $dumper): DumperInterface
    {
        $class = $this->config['dumpers'][$dumper];

        return new $class();
    }
}
