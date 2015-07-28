<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator;

use Spiral\Core\Component;
use Spiral\Core\HippocampusInterface;
use Spiral\Files\FilesInterface;

abstract class AbstractImporter extends Component implements ImporterInterface
{
    /**
     * Language can be set automatically during parsing import file or data, or manually before
     * importing bundles. Should be valid language id and have presets section in i18n configuration.
     *
     * @var string
     */
    protected $language = '';

    /**
     * Collected language bundles, bundle define list of associations between primary and currently
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
     * HippocampusInterface instance.
     *
     * @var HippocampusInterface
     */
    protected $runtime = null;

    /**
     * FilesInterface component.
     *
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * New importer instance.
     *
     * @param Translator           $translator
     * @param HippocampusInterface $runtime
     * @param FilesInterface       $files
     */
    public function __construct(
        Translator $translator,
        HippocampusInterface $runtime,
        FilesInterface $files
    )
    {
        $this->translator = $translator;
        $this->runtime = $runtime;
        $this->files = $files;
    }

    /**
     * Manually force import language id. Should be valid language id and have presets section in i18n
     * configuration. All bundles will be imported to that language directory.
     *
     * @param string $language Valid language id.
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Detected or manually defined language. Should be valid language ID and have presets section in
     * translator configuration. All bundles will be imported to that language directory.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Open import file.
     *
     * @param string $filename
     * @return self
     * @throws TranslatorException
     */
    public function open($filename)
    {
        if (!$this->files->exists($filename))
        {
            throw new TranslatorException(
                "Unable import translator bundles from '{$filename}', file does not exists."
            );
        }

        $this->parseStrings($filename);

        return $this;
    }

    /**
     * Method should read language bundles from specified filename and format them in an appropriate
     * way. Language has to be automatically detected during parsing, however it can be redefined
     * manually after.
     *
     * @param string $filename
     * @return array
     */
    abstract protected function parseStrings($filename);

    /**
     * Upload parsed data to target language bundles, language should be detected during parsing or
     * can be specified manually after (import destination will be changed accordingly).
     *
     * @param bool $replace If true imported data should replace existed bundle translations entirely.
     * @throws TranslatorException
     */
    public function import($replace = false)
    {
        if (empty($this->language))
        {
            throw new TranslatorException("Unable to perform bundles import, no language detected.");
        }

        if (!isset($this->translator->getConfig()['languages'][$this->language]))
        {
            throw new TranslatorException(
                "Unable to import language '{$this->language}', no presets found."
            );
        }

        $directory = $this->translator->getConfig()['languages'][$this->language]['directory'];
        foreach ($this->bundles as $bundle => $strings)
        {
            if (!$replace && !empty($existed = $this->runtime->loadData($bundle, $directory)))
            {
                $strings = $strings + $existed;
            }

            $this->runtime->saveData($bundle, $strings, $directory);
        }
    }
}