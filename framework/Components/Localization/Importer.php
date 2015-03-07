<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Localization;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Core;

abstract class Importer extends Component
{
    /**
     * Language can be set automatically during parsing import file or data, or manually before importing bundles. Should
     * be valid language id and have presets section in i18n configuration.
     *
     * @var string
     */
    protected $language = '';

    /**
     * Collected language bundles, bundle define list of associations between primary and currently selected language.
     * Bundles can be also used for "internal translating" (en => en).
     *
     * @var array
     */
    protected $bundles = array();
    /**
     * I18n component.
     *
     * @var I18nManager
     */
    protected $i18n = null;

    /**
     * Core instance.
     *
     * @var Core
     */
    protected $core = null;

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
     * @param I18nManager $i18n
     * @param Core        $core
     * @param FileManager $file
     */
    public function __construct(I18nManager $i18n, Core $core, FileManager $file)
    {
        $this->i18n = $i18n;
        $this->core = $core;
        $this->file = $file;
        $this->i18nConfig = $i18n->getConfig();
    }

    /**
     * Detected or manually defined language. Should be valid language id andhave presets section in i18n configuration.
     * All bundles will be imported to that language directory.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Manually force import language id. Should be valid language id and have presets section in i18n configuration. All
     * bundles will be imported to that language directory.
     *
     * @param string $language Valid language id.
     * @return string
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Method should read language bundles from specified filename and format them in an appropriate way. Language has to
     * be automatically detected during parsing, however it can be redefined manually after.
     *
     * @param string $filename
     * @return array
     */
    abstract protected function parseData($filename);

    /**
     * Opening file.
     *
     * @param string $filename
     * @return static
     * @throws LocalizationException
     */
    public function openFile($filename)
    {
        if (!$this->file->exists($filename))
        {
            throw new LocalizationException("Unable import i18n bundles from '{$filename}', file not exists.");
        }

        $this->parseData($filename);

        return $this;
    }

    /**
     * Upload parsed data to target language bundles, language will be detected during parsing or can be specified manually
     * after (import destination will be changed accordingly). Import will replace already existed bundles with or without
     * merging.
     *
     * @param bool $mergeBundles If true data from existed bundle will merged with imported one, if false imported will
     *                           completely replace old values.
     * @throws LocalizationException
     */
    public function importBundles($mergeBundles = true)
    {
        if (!$this->language)
        {
            throw new LocalizationException("Unable to provide bundles import, no language detected.");
        }

        if (!isset($this->i18nConfig['languages'][$this->language]))
        {
            throw new LocalizationException("Unable to import language '{$this->language}', no presets found.");
        }

        $bundlesFolder = $this->i18nConfig['languages'][$this->language]['dataFolder'];
        foreach ($this->bundles as $bundle => $data)
        {
            if ($mergeBundles && $oldData = $this->core->loadData($bundle, $bundlesFolder))
            {
                $data = $data + $oldData;
            }

            $this->file->ensureDirectory($bundlesFolder, FileManager::RUNTIME);
            $this->core->saveData($bundle, $data, $bundlesFolder);
        }
    }
}