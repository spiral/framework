<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Twig\Cache\CacheInterface as TwigCacheInterface;

class TwigCache implements TwigCacheInterface
{
    /** @var string */
    private $directory;

    /** @var FilesInterface */
    private $files;

    /**
     * @param string         $directory
     * @param FilesInterface $files
     */
    public function __construct(string $directory, FilesInterface $files = null)
    {
        $this->directory = $directory;
        $this->files = $files ?? new Files();
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey($name, $className)
    {
        $prefix = sprintf("%s:%s", $name, $className);
        $prefix = preg_replace('/([^A-Za-z0-9]|\-)+/', '-', $prefix);

        return sprintf("%s/%s.php", rtrim($this->directory, '/') . '/', $prefix);
    }

    /**
     * Delete cached files.
     *
     * @param string $name
     * @param string $className
     */
    public function delete($name, $className)
    {
        try {
            $this->files->delete($this->generateKey($name, $className));
        } catch (\Throwable $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($key, $content)
    {
        $this->files->write($key, $content, FilesInterface::RUNTIME, true);
    }

    /**
     * {@inheritdoc}
     */
    public function load($key)
    {
        if ($this->files->exists($key)) {
            include_once $key;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($key)
    {
        if ($this->files->exists($key)) {
            return $this->files->time($key);
        }

        return 0;
    }
}