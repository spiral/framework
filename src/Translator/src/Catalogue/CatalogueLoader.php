<?php

declare(strict_types=1);

namespace Spiral\Translator\Catalogue;

use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Translator\Catalogue;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Symfony\Component\Finder\Finder;

final class CatalogueLoader implements LoaderInterface
{
    use LoggerTrait;

    public function __construct(
        private readonly TranslatorConfig $config
    ) {
    }

    public function hasLocale(string $locale): bool
    {
        $locale = \preg_replace('/[^a-zA-Z_]/', '', \mb_strtolower($locale));

        foreach ($this->getDirectories() as $directory) {
            if (\is_dir($this->config->getLocaleDirectory($locale, $directory))) {
                return true;
            }
        }

        return false;
    }

    public function getLocales(): array
    {
        $directories = $this->getDirectories();
        if ($directories === []) {
            return [];
        }

        $finder = new Finder();
        $locales = [];
        foreach ($finder->in($directories)->directories() as $directory) {
            $locales[] = $directory->getFilename();
        }

        return \array_unique($locales);
    }

    public function loadCatalogue(string $locale): CatalogueInterface
    {
        $locale = \preg_replace('/[^a-zA-Z_]/', '', \mb_strtolower($locale));
        $catalogue = new Catalogue($locale);

        if (!$this->hasLocale($locale)) {
            return $catalogue;
        }

        $directories = [];
        foreach ($this->getDirectories() as $directory) {
            if (\is_dir($this->config->getLocaleDirectory($locale, $directory))) {
                $directories[] = $this->config->getLocaleDirectory($locale, $directory);
            }
        }

        $finder = new Finder();
        foreach ($finder->in($directories)->files() as $file) {
            $this->getLogger()->info(
                \sprintf(
                    "found locale domain file '%s'",
                    $file->getFilename()
                ),
                ['file' => $file->getFilename()]
            );

            //Per application agreement domain name must present in filename
            $domain = \strstr($file->getFilename(), '.', true);

            if (!$this->config->hasLoader($file->getExtension())) {
                $this->getLogger()->warning(
                    \sprintf(
                        "unable to load domain file '%s', no loader found",
                        $file->getFilename()
                    ),
                    ['file' => $file->getFilename()]
                );

                continue;
            }

            $catalogue->mergeFrom(
                $this->config->getLoader($file->getExtension())->load(
                    (string)$file,
                    $locale,
                    $domain
                )
            );
        }

        return $catalogue;
    }

    /**
     * @return array<array-key, non-empty-string>
     */
    private function getDirectories(): array
    {
        $directories = [];
        if (\is_dir($this->config->getLocalesDirectory())) {
            $directories[] = $this->config->getLocalesDirectory();
        }

        foreach ($this->config->getDirectories() as $directory) {
            if (\is_dir($directory)) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }
}
