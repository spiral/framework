<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Stempler;

use Spiral\Files\FilesInterface;
use Spiral\Views\EnvironmentInterface;

/**
 * Very simple Stempler cache. Almost identical to twig cache except generateKey method.
 */
class StemplerCache
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
     * @param string $path
     *
     * @return string
     */
    public function cacheFilename(string $path): string
    {
        $hash = hash('md5', $path . '.' . $this->environment->getID());

        return $this->environment->cacheDirectory() . $hash . '.php';
    }

    /**
     * Last update time.
     *
     * @param string $key
     *
     * @return int
     */
    public function timeCached(string $key): int
    {
        if (!$this->environment->isCachable()) {
            //Always expired
            return 0;
        }

        if ($this->files->exists($key)) {
            return $this->files->time($key);
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