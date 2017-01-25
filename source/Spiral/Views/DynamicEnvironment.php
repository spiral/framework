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
 * Declares set of dependencies for environment and cache keys based on associated container.
 *
 * Attention, dependency set is stated as immutable, THOUGHT calculated values DO depend on
 * container and might change in application lifetime.
 */
class DynamicEnvironment implements EnvironmentInterface
{
    /**
     * Registered dependencies.
     *
     * @var array
     */
    private $dependencies = [];

    /**
     * @var bool
     */
    private $cachable = true;

    /**
     * @var string
     */
    private $cacheDirectory = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param array              $dependencies
     * @param bool               $cachable
     * @param string             $cacheDirectory
     * @param ContainerInterface $container
     */
    public function __construct(
        array $dependencies,
        bool $cachable,
        string $cacheDirectory,
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
    public function isCachable(): bool
    {
        return $this->cachable;
    }

    /**
     * @return string
     */
    public function cacheDirectory(): string
    {
        return $this->cacheDirectory;
    }

    /**
     * {@inheritdoc}
     *
     * You can add dependency to a function, closure, or callable pair where first argument is
     * binding name (resolved thought container).
     */
    public function withDependency(string $dependency, callable $source): EnvironmentInterface
    {
        $environment = clone $this;
        $environment->dependencies[$dependency] = $source;

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(string $dependency)
    {
        if (!isset($this->dependencies[$dependency])) {
            throw new EnvironmentException("Undefined environment variable '{$dependency}'");
        }

        $source = $this->dependencies[$dependency];

        //Let's resolve using container
        if (is_array($source) && is_string($source[0])) {
            $source[0] = $this->container->get($source[0]);
            $this->dependencies[$dependency] = $source;
        }

        return call_user_func($source);
    }

    /**
     * {@inheritdoc}
     */
    public function getID(): string
    {
        $calculated = '';
        foreach ($this->dependencies as $dependency => $source) {
            $calculated .= "[{$dependency}={$this->getValue($dependency)}]";
        }

        return md5($calculated);
    }
}