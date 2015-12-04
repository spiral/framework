<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Translator;

use Spiral\Core\Component;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FilesInterface;
use Spiral\Translator\Configs\TranslatorConfig;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Can load translation data from multiple different formats.
 */
class TranslationSource extends Component implements SourceInterface
{
    /**
     * Log messages.
     */
    use LoggerTrait;

    /**
     * @var TranslatorConfig
     */
    protected $config = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param TranslatorConfig $config
     * @param FilesInterface   $files
     */
    public function __construct(TranslatorConfig $config, FilesInterface $files)
    {
        $this->config = $config;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale($locale)
    {
        return $this->files->exists($this->config->localeDirectory($locale));
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        $finder = new Finder();
        $finder->in($this->config->localesDirectory())->directories();

        $locales = [];
        /**
         * @var SplFileInfo $directory
         */
        foreach ($finder->directories()->getIterator() as $directory) {
            $locales[] = $directory->getFilename();
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocale($locale)
    {
        $domains = [];

        $finder = new Finder();
        $finder->in($this->config->localeDirectory($locale));

        /**
         * @var SplFileInfo $file
         */
        foreach ($finder->getIterator() as $file) {
            $this->logger()->info("Found locale domain file '{file}'", [
                'file' => $file->getFilename()
            ]);

            //Per application agreement domain name must present in filename
            $domain = strstr($file->getFilename(), '.', true);

            if ($this->config->hasLoader($file->getExtension())) {
                $loader = $this->config->loaderClass($file->getExtension());
                $domains[$domain] = $this->loadCatalogue($locale, $domain, $file, new $loader());
            }
        }

        return $domains;
    }

    /**
     * Load domain data from file.
     *
     * @param string          $locale
     * @param string          $domain
     * @param SplFileInfo     $file
     * @param LoaderInterface $loader
     * @return MessageCatalogue
     */
    protected function loadCatalogue($locale, $domain, SplFileInfo $file, LoaderInterface $loader)
    {
        return $loader->load((string)$file, $locale, $domain);
    }
} 