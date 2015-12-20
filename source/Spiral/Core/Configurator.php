<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\ConfiguratorException;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Files\FilesInterface;

/**
 * Responsible for configuration loading. All configs are automatically cached (temporary
 * disabled!).
 *
 * @see InjectableConfig
 */
class Configurator extends Component implements ConfiguratorInterface
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
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @var EnvironmentInterface
     */
    protected $environment = null;

    /**
     * @param string               $directory
     * @param FilesInterface       $files
     * @param HippocampusInterface $memory
     * @param EnvironmentInterface $environment
     */
    public function __construct(
        $directory,
        FilesInterface $files,
        HippocampusInterface $memory,
        EnvironmentInterface $environment
    ) {
        $this->directory = $directory;
        $this->files = $files;
        $this->memory = $memory;
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($section = null)
    {
        $filename = $this->configFilename($section);

        if (!$this->files->exists($filename)) {
            throw new ConfiguratorException(
                "Unable to load '{$section}' configuration, file not found."
            );
        }

        //@todo restore caching
        return $this->loadConfig($section, $filename);
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
        $config = $this->getConfig($class->getConstant('CONFIG'));

        return $this->configs[$class->getName()] = $class->newInstance($config);
    }

    /**
     * Drop all cached configs (in RAM).
     */
    public function flushCache()
    {
        $this->configs = [];
    }

    /**
     * @param string $config
     * @return string
     */
    protected function configFilename($config)
    {
        return $this->directory . $config . static::EXTENSION;
    }

    /**
     * Load config data from file
     *
     * @param string $config
     * @param string $filename
     * @return array
     */
    protected function loadConfig($config, $filename)
    {
        //todo: support more config types, maybe yaml?
        $data = require($this->files->localUri($filename));

        if (!is_array($data)) {
            throw  new ConfiguratorException("Config '{$config}' does not contain array data");
        }

        return $data;
    }
}