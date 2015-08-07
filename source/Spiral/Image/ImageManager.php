<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Image;

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager as InterventionManager;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Singleton;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\Files\Streams\StreamWrapper;

/**
 * Simple functionality wrapper at top of InterventionManager, support PSR7 streams as image source.
 */
class ImageManager extends Singleton
{
    /**
     * Declaring singleton to IoC.
     */
    const SINGLETON = self::class;

    /**
     * @var InterventionManager
     */
    protected $intervention = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param InterventionManager   $intervention
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        InterventionManager $intervention
    ) {
        $this->intervention = $intervention;
        $this->intervention->configure($configurator->getConfig('image'));
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->intervention->config;
    }

    /**
     * @param array $config
     * @return static
     */
    public function setConfig(array $config = [])
    {
        $this->intervention->configure($config);

        return $this;
    }

    /**
     * Create intervention image based on provided source.
     *
     * @param mixed|UploadedFileInterface|StreamInterface|StreamableInterface $data
     * @return Image
     */
    public function open($data)
    {
        if ($data instanceof UploadedFileInterface || $data instanceof StreamableInterface) {
            $data = $data->getStream();
        }

        if ($data instanceof StreamInterface) {
            $data = StreamWrapper::getUri($data);
        }

        return $this->intervention->make($data);
    }

    /**
     * Check if provided data source is valid image.
     *
     * @param mixed|UploadedFileInterface|StreamInterface|StreamableInterface $data
     * @return bool
     */
    public function isImage($data)
    {
        try {
            $this->open($data);

            return true;
        } catch (NotReadableException $exception) {
            return false;
        }
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
}