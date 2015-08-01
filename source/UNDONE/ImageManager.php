<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Image;

use Intervention\Image\Image;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Components\Files\StreamContainerInterface;
use Spiral\Components\Modules\Module;
use Spiral\Components\Files\StreamWrapper;
use Spiral\Components\Modules\Definition;
use Spiral\Components\Modules\Installer;
use Spiral\Core\Component\SingletonTrait;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Support\Generators\Config\ConfigWriter;
use Intervention\Image\ImageManager as InterventionManager;

class ImageManager extends Module
{
    /**
     * This is singleton.
     */
    use SingletonTrait;

    /**
     * Declaring singleton to IoC.
     */
    const SINGLETON = self::class;

    /**
     * Intervention ImageManager.
     *
     * @var InterventionManager
     */
    protected $intervention = null;

    /**
     * Configuring module.
     *
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->intervention = new InterventionManager($configurator->getConfig($this));
    }

    /**
     * Current component configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->intervention->config;
    }

    /**
     * Overrides configuration settings.
     *
     * @param array $config
     * @return static
     */
    public function setConfig(array $config = [])
    {
        $this->intervention->configure($config);

        return $this;
    }

    /**
     * Initiates an Image instance from different input types. Method support UploadedFiles,
     * StorageObjects, Streams, local files, resources and binary strings.
     *
     * @param mixed|UploadedFileInterface|StreamInterface|StreamContainerInterface $data
     * @return Image
     */
    public function open($data)
    {
        if ($data instanceof UploadedFileInterface || $data instanceof StreamContainerInterface)
        {
            $data = $data->getStream();
        }

        if ($data instanceof StreamInterface)
        {
            $data = StreamWrapper::getUri($data);
        }

        return $this->intervention->make($data);
    }

    /**
     * Creates an empty image canvas.
     *
     * @param  integer $width
     * @param  integer $height
     * @param  mixed   $background
     * @return Image
     */
    public function canvas($width, $height, $background = null)
    {
        return $this->intervention->canvas($width, $height, $background);
    }

    /**
     * Create new cached image and run callback (requires additional package intervention/imagecache).
     *
     * @param \Closure $callback
     * @param integer  $lifetime
     * @param boolean  $returnObj
     * @return Image
     */
    public function cache(\Closure $callback, $lifetime = null, $returnObj = false)
    {
        return $this->intervention->cache($callback, $lifetime, $returnObj);
    }

    /**
     * Module installer responsible for operations like copying resources, registering configs, view
     * namespaces and declaring that bootstrap() call is required.
     * This method is static as it should be called without constructing module object.
     *
     * @param Definition $definition Module definition fetched or generated of composer file.
     * @return Installer
     */
    public static function getInstaller(Definition $definition)
    {
        $installer = parent::getInstaller($definition);

        $imageConfig = ConfigWriter::make([
            'name'   => 'image',
            'method' => ConfigWriter::MERGE_REPLACE
        ])->readConfig(
            $definition->getLocation() . '/config'
        );

        //Adding config file
        return $installer->registerConfig($imageConfig);
    }
}