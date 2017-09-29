<?php
/**
 * (c) Lev Seleznev (tuneyourserver) 2017
 */

namespace Spiral\Views;


use Spiral\Files\FileManager;
use Spiral\Views\Configs\ViewsConfig;

/**
 * Class ViewCacheLocator
 *
 * Provides ability to locate cache files by view name and namespace
 *
 * @package Spiral\Views
 */
class ViewCacheLocator extends AbstractViewCache
{
    /** @var FileManager  */
    protected $fileManager;

    /** @var ViewsConfig  */
    protected $config;

    /**
     * CacheLocator constructor.
     *
     * @param ViewsConfig $config
     * @param FileManager $fileManager
     */
    public function __construct(ViewsConfig $config, FileManager $fileManager)
    {
        $this->config = $config;
        $this->fileManager = $fileManager;
    }

    /**
     * Returns all cache files for view.
     *
     * @param string $view
     * @param string $namespace
     * @return array
     */
    public function getFiles($view, $namespace = 'default')
    {
        $prefix = $this->getPrefix($view, $namespace);

        return $this->fileManager->getFiles($this->config->cacheDirectory(), $prefix.'-*');
    }
}