<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

use Spiral\Core\ContainerInterface;
use Spiral\Views\Exceptions\EnvironmentException;

/**
 * Default implementation of EnvironmentInterface
 */
class ViewEnvironment implements EnvironmentInterface
{
    /**
     * Registered dependencies.
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * @var bool
     */
    protected $cachable = true;

    /**
     * @var string
     */
    protected $cacheDirectory = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param array              $dependencies
     * @param string             $cachable
     * @param string             $cacheDirectory
     * @param ContainerInterface $container
     */
    public function __construct(
        array $dependencies,
        $cachable,
        $cacheDirectory,
        ContainerInterface $container
    ) {
        $this->dependencies = $dependencies;
        $this->cachable = $cachable;
        $this->cacheDirectory = $cacheDirectory;
        $this->container = $container;
    }

    /**
     * Check if caching is enabled.
     *
     * @return bool
     */
    public function cachable()
    {
        return $this->cachable;
    }

    /**
     * @return string
     */
    public function cacheDirectory()
    {
        return $this->cacheDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function addDependency($dependency, callable $source)
    {
        $this->dependencies[$dependency] = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($dependency)
    {
        if (!isset($this->dependencies[$dependency])) {
            throw new EnvironmentException("Undefined environment variable '{$dependency}'.");
        }

        $source = $this->dependencies[$dependency];

        if (is_array($source) && is_string($source[0])) {
            //Let's resolve using container
            $source[0] = $this->container->get($source[0]);
            $this->dependencies[$dependency] = $source;
        }

        return call_user_func($source);
    }

    /**
     * {@inheritdoc}
     */
    public function getID()
    {
        $calculated = '';
        foreach ($this->dependencies as $dependency => $source) {
            $calculated .= "[{$dependency}={$this->getValue($dependency)}]";
        }

        return md5($calculated);
    }
}