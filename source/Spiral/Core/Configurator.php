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
 * Responsible for configuration loading.
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
     * Needed for container scope.
     *
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param string             $directory
     * @param FilesInterface     $files
     * @param ContainerInterface $container Needed to set proper scope at moment of config parsing.
     */
    public function __construct(
        string $directory,
        FilesInterface $files,
        ContainerInterface $container
    ) {
        $this->directory = $directory;
        $this->files = $files;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(string $section = null): array
    {
        $filename = $this->configFilename($section);

        if (!$this->files->exists($filename)) {
            throw new ConfiguratorException(
                "Unable to load '{$section}' configuration, file not found"
            );
        }

        $scope = self::staticContainer($this->container);
        try {
            //Configs are loaded in a defined GLOBAL container scope
            return $this->loadConfig($section, $filename);
        } finally {
            self::staticContainer($scope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
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
     *
     * @return string
     */
    protected function configFilename(string $config): string
    {
        return $this->directory . $config . static::EXTENSION;
    }

    /**
     * Load config data from file
     *
     * @param string $config
     * @param string $filename
     *
     * @return array
     */
    protected function loadConfig(string $config, string $filename): array
    {
        /**
         * Altering this method will provide ability to support more config types, config classes
         * can be left untouched.
         */
        $data = require($this->files->localPath($filename));

        if (!is_array($data)) {
            throw  new ConfiguratorException("Config '{$config}' does not contain array data");
        }

        return $data;
    }
}