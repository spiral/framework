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
use Spiral\Translator\Exceptions\ExporterException;
use Spiral\Translator\ExporterInterface;
use Spiral\Translator\Translator;

/**
 * Abstract implementation of ExporterInterface with ability to compile loaded bundles into string
 * using specified format.
 */
abstract class AbstractExporter extends Component implements ExporterInterface
{
    /**
     * Language being exported.
     *
     * @var string
     */
    private $language = '';

    /**
     * Language bundles to be exported.
     *
     * @var array
     */
    protected $bundles = [];

    /**
     * @var Translator
     */
    protected $translator = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param Translator     $translator
     * @param FilesInterface $files
     */
    public function __construct(Translator $translator, FilesInterface $files)
    {
        $this->translator = $translator;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function load($language, $prefix = '')
    {
        if (!isset($this->translator->config()['languages'][$language]))
        {
            throw new ExporterException("Unable to export language '{$language}', no presets found.");
        }

        $this->language = $language;
        $this->bundles = $this->loadBundles($language, $prefix);

        if ($this->language == $this->translator->config()['default'])
        {
            return $this;
        }

        $defaultBundles = $this->loadBundles($this->translator->config()['default'], $prefix);
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
     * {@inheritdoc}
     */
    public function export($filename)
    {
        if (empty($this->language))
        {
            throw new ExporterException("No language specified to be exported.");
        }

        $this->files->write($filename, $this->compile());
    }

    /**
     * Language being exported.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Compile exported language into internal format to be written on hard drive.
     *
     * @return string
     */
    abstract protected function compile();

    /**
     * Load all bundle strings from specified language.
     *
     * @param string $language
     * @param string $prefix Only bundle names started with this prefix will be exported.
     * @return array
     */
    private function loadBundles($language, $prefix = '')
    {
        $bundles = $this->files->getFiles(
            $this->translator->config()['languages'][$language]['directory']
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
}