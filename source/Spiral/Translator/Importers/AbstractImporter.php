<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator\Importers;

use Spiral\Core\Component;
use Spiral\Core\HippocampusInterface;
use Spiral\Files\FilesInterface;
use Spiral\Translator\Exceptions\ImporterException;
use Spiral\Translator\ImporterInterface;
use Spiral\Translator\Translator;

/**
 * Generic implementation of Importer interface.
 */
abstract class AbstractImporter extends Component implements ImporterInterface
{
    /**
     * Target import language.
     *
     * @var string
     */
    private $language = '';

    /**
     * Every parsed bundle has to be collected in this array.
     *
     * @var array
     */
    protected $bundles = [];

    /**
     * @var Translator
     */
    protected $translator = null;

    /**
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param Translator           $translator
     * @param HippocampusInterface $memory
     * @param FilesInterface       $files
     */
    public function __construct(
        Translator $translator,
        HippocampusInterface $memory,
        FilesInterface $files
    )
    {
        $this->translator = $translator;
        $this->memory = $memory;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function open($filename)
    {
        if (!$this->files->exists($filename))
        {
            throw new ImporterException(
                "Unable import translator bundles from '{$filename}', file does not exists."
            );
        }

        $this->parseStrings($this->files->read($filename));

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function import($replace = false)
    {
        if (empty($this->language))
        {
            throw new ImporterException("Unable to perform bundles import, no language detected.");
        }

        if (!isset($this->translator->config()['languages'][$this->language]))
        {
            throw new ImporterException(
                "Unable to import language '{$this->language}', no presets found."
            );
        }

        $directory = $this->translator->config()['languages'][$this->language]['directory'];
        foreach ($this->bundles as $bundle => $strings)
        {
            if (!$replace && !empty($existed = $this->memory->loadData($bundle, $directory)))
            {
                $strings = $strings + $existed;
            }

            $this->memory->saveData($bundle, $strings, $directory);
        }
    }

    /**
     * Parse file source to fetch language bundles and strings.
     *
     * @param string $source
     */
    abstract protected function parseStrings($source);
}