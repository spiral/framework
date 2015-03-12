<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\Image\ImageManager;
use Spiral\Components\Image\ImageObject;
use Spiral\Components\Image\ProcessorInterface;
use Spiral\Core\Facade;

/**
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static ImageObject open(string $filename)
 * @method static ProcessorInterface imageProcessor(string $filename, string $type = '')
 * @method static string getAlias()
 * @method static ImageManager make(array $parameters = array())
 * @method static ImageManager getInstance()
 */
class Image extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'image';
}