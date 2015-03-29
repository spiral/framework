<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\I18n;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Core;

abstract class Exporter extends Component
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
    protected $bundles = array();

    /**
     * I18nManager component.
     *
     * @var Translator
     */
    protected $i18n = null;

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * I18n component configuration.
     *
     * @var array
     */
    protected $i18nConfig = array();

    /**
     * New indexer instance.
     *
     * @param Translator $i18n
     * @param FileManager $file
     */
    public function __construct(Translator $i18n, FileManager $file)
    {
        $this->i18n = $i18n;
        $this->file = $file;
        $this->i18nConfig = $i18n->getConfig();
    }

    /**
     * Load all language bundles to memory for future exporting in specified format.
     *
     * @param string $language Should be valid language id.
     * @param string $prefix   Only bundle names started with this prefix will be exported.
     * @return static
     * @throws I18nException
     */
    public function loadLanguage($language, $prefix = '')
    {
        if (!isset($this->i18nConfig['languages'][$language]))
        {
            throw new I18nException(
                "Unable to export language '{$language}', no presets found."
            );
        }

        $this->language = $language;
        $this->bundles = $this->loadBundles($language, $prefix);

        if ($this->language != $this->i18nConfig['default'])
        {
            foreach ($this->loadBundles($this->i18nConfig['default'], $prefix) as $bundle => $data)
            {
                if (!isset($this->bundles[$bundle]))
                {
                    $this->bundles[$bundle] = array();
                }

                //Merging with values from default (primary) language
                $this->bundles[$bundle] = $this->bundles[$bundle] + $data;
            }
        }

        return $this;
    }

    /**
     * Load all bundles from specified language.
     *
     * @param string $language Language id.
     * @param string $prefix   Only bundle names started with this prefix will be exported.
     * @return array
     */
    protected function loadBundles($language, $prefix = '')
    {
        $bundles = array();

        $files = $this->file->getFiles(
            $this->i18nConfig['languages'][$language]['dataFolder'],
            array(substr(Core::RUNTIME_EXTENSION, 1))
        );

        foreach ($files as $filename)
        {
            $bundle = substr(basename($filename), 0, -4);

            if (!empty($prefix) && stripos($bundle, $prefix) !== 0)
            {
                continue;
            }

            $bundles[$bundle] = (include $filename);
        }

        return $bundles;
    }

    /**
     * Export collected bundles data to specified file using format described by exporter.
     *
     * @param string $filename
     * @return mixed
     */
    abstract public function exportBundles($filename);
}