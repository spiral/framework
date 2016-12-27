<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines\Twig;

use Spiral\Files\FilesInterface;
use Spiral\Views\EnvironmentInterface;

/**
 * Spiral specific twig cache. OpCache reset not included yet.
 */
class TwigCache implements \Twig_CacheInterface
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
     * @param FilesInterface       $files
     * @param EnvironmentInterface $environment
     */
    public function __construct(FilesInterface $files, EnvironmentInterface $environment)
    {
        $this->files = $files;
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey($name, $className)
    {
        $hash = hash('md5', $className . '.' . $this->environment->getID());

        return $this->environment->cacheDirectory() . '/' . $hash[0] . $hash[1] . '/' . $hash . '.php';
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
            include_once $this->files->localPath($key);
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