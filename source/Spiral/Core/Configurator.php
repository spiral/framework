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
 * Responsible for configuration loading. All configs are automatically cached (temporary
 * disabled!).
 *
 * @see InjectableConfig
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
     * Cached configs.
     *
     * @var array
     */
    protected $configs = [];

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param string         $directory
     * @param FilesInterface $files
     */
    public function __construct($directory, FilesInterface $files)
    {
        $this->directory = $directory;
        $this->files = $files;
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
        if (isset($this->configs[$class->getName()])) {
            return $this->configs[$class->getName()];
        }

        //Due internal contract we can fetch config section from class constant
        $config = $this->getConfig($class->getConstant('CONFIG'), false);

        if ($config instanceof ConfigInterface) {
            //Apparently config file contain class definition (let's hope this is same config class)
            return $config;
        }

        return $this->configs[$class->getName()] = $class->newInstance($config);
    }

    /**
     * Drop all cached configs (in RAM).
     */
    public function flushCache()
    {
        $this->configs = [];
    }
}