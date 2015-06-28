<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image;

use Intervention\Image\ImageManager as BaseImageManager;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Components\Storage\StorageObject;
use Spiral\Core\Component\SingletonTrait;
use Spiral\Core\Configurator;

class ImageManager extends BaseImageManager
{
    /**
     * Component traits.
     */
    use  SingletonTrait;

    /**
     * This class is singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Constructing image manager.
     *
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        parent::__construct($configurator->getConfig('image'));
    }

    /**
     * Initiates an Image instance from different input types, method is altered in spiral to
     * support PSR7 streams and UploadedFileInterface. This might be changed in future if library
     * author will support PSR7 by himself.
     *
     * @param mixed|StreamInterface|UploadedFileInterface|StorageObject $data
     * @return \Intervention\Image\Image
     */
    public function make($data)
    {
        return parent::make($data);
    }

    /**
     * Initiates an Image instance from different input types, alias for make method.
     *
     * @param  mixed $data
     * @return \Intervention\Image\Image
     */
    public function open($data)
    {
        return $this->make($data);
    }
}