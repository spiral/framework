<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image;

use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;

class ImageManager extends Component
{
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * New image component instance.
     *
     * @param CoreInterface $core
     * @param Container     $container
     */
    public function __construct(CoreInterface $core, Container $container)
    {
        $this->config = $core->loadConfig('image');
        $this->container = $container;
    }

    /**
     * Current component configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Update config with new values, new configuration will be merged with old one.
     *
     * @param array $config
     * @return array
     */
    public function setConfig(array $config)
    {
        return $this->config = $config + $this->config;
    }

    /**
     * Open existed filename and create ImageObject based on it, ImageObject->isSupported() method
     * can be used to verify that file is supported and can be processed. ImageObject preferred to
     * be used for processing existed images, rather that creating new.
     *
     * @param string $filename Local image filename.
     * @return ImageObject
     */
    public function open($filename)
    {
        return ImageObject::make(compact('filename'), $this->container);
    }

    /**
     * Image processor represents operations associated with one specific image file, all processing
     * operation (resize, crop and etc) described via operations sequence and perform on image save,
     * every ImageObject will have it's own processor.
     *
     * Every processor will implement set of pre-defined operations, however additional operations
     * can be supported by processor and extend default set of image manipulations.
     *
     * @param string $filename Local image filename.
     * @param string $type     Forced processor id.
     * @return ProcessorInterface
     */
    public function imageProcessor($filename, $type = '')
    {
        $type = $type ?: $this->config['processor'];
        $config = $this->config['processors'][$type];

        return $this->container->get($config['class'], compact('filename', 'config'));
    }
}