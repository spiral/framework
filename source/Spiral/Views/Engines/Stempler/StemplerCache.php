<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Stempler;

use Spiral\Files\FilesInterface;
use Spiral\Views\AbstractViewCache;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ViewSource;

/**
 * Very simple Stempler cache. Almost identical to twig cache except generateKey method.
 */
class StemplerCache extends AbstractViewCache
{
    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var EnvironmentInterface
     */
    protected $environment = null;

    /**
     * @param EnvironmentInterface $environment
     * @param FilesInterface       $files
     */
    public function __construct(EnvironmentInterface $environment, FilesInterface $files)
    {
        $this->files = $files;
        $this->environment = $environment;
    }

    /**
     * @param EnvironmentInterface $environment
     *
     * @return StemplerCache
     */
    public function withEnvironment(EnvironmentInterface $environment): StemplerCache
    {
        $cache = clone $this;
        $cache->environment = $environment;

        return $cache;
    }

    /**
     * Generate cache filename for given path.
     *
     * @param ViewSource $context
     *
     * @return string
     */
    public function cacheFilename(ViewSource $context): string
    {
        $hash = hash('md5', $context->getFilename() . '.' . $this->environment->getID());

        return $this->environment->cacheDirectory() .
            $this->getPrefix($context->getName(), $context->getNamespace()) . '-' . $hash . '.php';
    }

    /**
     * Last update time.
     *
     * @param string $cacheFilename
     *
     * @return int
     */
    public function timeCached(string $cacheFilename): int
    {
        if (!$this->environment->isCachable()) {
            //Always expired
            return 0;
        }

        if ($this->files->exists($cacheFilename)) {
            return $this->files->time($cacheFilename);
        }

        return 0;
    }

    /**
     * Store data into cache.
     *
     * @param string $cacheFilename
     * @param string $content
     */
    public function write(string $cacheFilename, string $content)
    {
        $this->files->write(
            $cacheFilename,
            $content,
            FilesInterface::RUNTIME,
            true
        );
    }
}