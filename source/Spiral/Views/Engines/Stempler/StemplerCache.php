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
     * TwigCache constructor.
     *
     * @param FilesInterface       $files
     * @param EnvironmentInterface $environment
     */
    public function __construct(FilesInterface $files, EnvironmentInterface $environment)
    {
        $this->files = $files;
        $this->environment = $environment;
    }

    /**
     * Generate cache key for given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function generateKey(string $path): string
    {
        $hash = hash('md5', $path . '.' . $this->environment->getID());

        return $this->environment->cacheDirectory() . '/' . $hash . '.php';
    }

    /**
     * Get local cache filename (to be included in view).
     *
     * @param string $key
     *
     * @return string
     */
    public function cachedFilename(string $key): string
    {
        return $this->files->localUri($key);
    }

    /**
     * Store data into cache.
     *
     * @param string $key
     * @param string $content
     */
    public function write(string $key, string $content)
    {
        $this->files->write($key, $content, FilesInterface::RUNTIME, true);
    }

    /**
     * Last update time.
     *
     * @param string $key
     *
     * @return int
     */
    public function getTimestamp(string $key): int
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
}