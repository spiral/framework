<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\ConfiguratorException;
use Spiral\Files\FilesInterface;

/**
 * Responsible for configuration loading. All configs automatically cached.
 */
class Configurator implements ConfiguratorInterface
{
    /**
     * Config files extension.
     */
    const EXTENSION = '.php';

    /**
     * @var string
     */
    private $directory = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @invisible
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @param string               $directory
     * @param FilesInterface       $files
     * @param HippocampusInterface $memory
     */
    public function __construct($directory, FilesInterface $files, HippocampusInterface $memory)
    {
        $this->directory = $directory;
        $this->files = $files;
        $this->memory = $memory;
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $toArray Always force array response.
     */
    public function getConfig($section = null, $toArray = true)
    {
        $filename = $this->directory . $section . static::EXTENSION;

        if (!$this->files->exists($filename)) {
            throw new ConfiguratorException(
                "Unable to load '{$section}' configuration, file not found."
            );
        }

        $data = require($this->files->localUri($filename));
        if ($toArray && $data instanceof ConfigInterface) {
            //getConfig method must always return arrays
            $data = $data->toArray();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function createInjection(\ReflectionClass $class, $context = null)
    {
        //Due internal contract we can fetch config section from class constant
        $config = $this->getConfig($class->getConstant('CONFIG'), false);

        if ($config instanceof ConfigInterface) {
            //Apparently config file contain class definition
            return $config;
        }

        return $class->newInstance($config);
    }
}