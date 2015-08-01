<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator\Exporters;

use Spiral\Core\Component;
use Spiral\Files\FilesInterface;
use Spiral\Translator\ExporterInterface;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorException;

abstract class AbstractExporter extends Component implements ExporterInterface
{
    /**
     * Language id to be exported, should be valid language id and have associated section in i18n
     * configuration.
     *
     * @var string
     */
    protected $language = '';

    /**
     * Language bundles to be exported, bundle define list of associations between primary and currently
     * selected language. Bundles can be also used for "internal translating" (en => en).
     *
     * @var array
     */
    protected $bundles = [];

    /**
     * Translator component.
     *
     * @var Translator
     */
    protected $translator = null;

    /**
     * FilesInterface component.
     *
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * New indexer instance.
     *
     * @param Translator     $translator
     * @param FilesInterface $files
     */
    public function __construct(Translator $translator, FilesInterface $files)
    {
        $this->translator = $translator;
        $this->files = $files;
    }

    /**
     * Load all bundle strings into memory for future exporting in specified format.
     *
     * @param string $language Should be valid language id.
     * @param string $prefix   Only bundle names started with this prefix will be exported.
     * @return self
     * @throws Translator
     */
    public function load($language, $prefix = '')
    {
        if (!isset($this->translator->getConfig()['languages'][$language]))
        {
            throw new TranslatorException("Unable to export language '{$language}', no presets found.");
        }

        $this->language = $language;
        $this->bundles = $this->loadBundles($language, $prefix);

        if ($this->language == $this->translator->getConfig()['default'])
        {
            return $this;
        }

        $defaultBundles = $this->loadBundles($this->translator->getConfig()['default'], $prefix);
        foreach ($defaultBundles as $bundle => $data)
        {
            if (!isset($this->bundles[$bundle]))
            {
                $this->bundles[$bundle] = [];
            }

            //Merging with values from default (primary) language
            $this->bundles[$bundle] = $this->bundles[$bundle] + $data;
        }

        return $this;
    }

    /**
     * Load all bundle strings from specified language.
     *
     * @param string $language Language id.
     * @param string $prefix   Only bundle names started with this prefix will be exported.
     * @return array
     */
    protected function loadBundles($language, $prefix = '')
    {
        $bundles = $this->files->getFiles(
            $this->translator->getConfig()['languages'][$language]['directory']
        );

        $result = [];
        foreach ($bundles as $filename)
        {
            $bundle = substr(basename($filename), 0, -1 * strlen($this->files->extension($filename)));
            if (!empty($prefix) && stripos($bundle, $prefix) !== 0)
            {
                continue;
            }

            try
            {
                $result[$bundle] = (include $filename);
            }
            catch (\Exception $exception)
            {
            }
        }

        return $result;
    }

    /**
     * Export collected bundle strings to specified file using format described by exporter.
     *
     * @param string $filename
     */
    abstract public function export($filename);
}