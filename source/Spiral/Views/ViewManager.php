<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\Container\ContainerException;
use Spiral\Core\Singleton;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Files\FilesInterface;
use Spiral\Views\Exceptions\ViewException;

/**
 * Default ViewsInterface implementation with ability to change cache versions via external value
 * dependencies. ViewManager support multiple namespaces and namespaces associated with multiple
 * folders.
 */
class ViewManager extends Singleton implements ViewsInterface
{
    /**
     * Configuration is required.
     */
    use ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Configuration section.
     */
    const CONFIG = 'views';

    /**
     * Constants to work with view cache.
     */
    const CACHE_FILENAME = 0;
    const CACHE_ENGINE   = 1;
    const CACHE_CLASS    = 2;

    /**
     * Namespaces associated with their locations.
     *
     * @var array
     */
    private $namespaces = [];

    /**
     * View cache file will depends on this set of values.
     *
     * @var array
     */
    private $dependencies = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     * @param FilesInterface        $files
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        ContainerInterface $container,
        FilesInterface $files
    ) {
        $this->config = $configurator->getConfig(static::CONFIG);

        $this->container = $container;
        $this->files = $files;

        //Namespaces can be edited in runtime
        $this->namespaces = $this->config['namespaces'];
    }

    /**
     * List of every view namespace associated with directories.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Add new view dependency. Every new dependency will change generated cache filename.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setDependency($name, $value)
    {
        $this->dependencies[$name] = $value;
    }

    /**
     * Get dependency value or return null.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getDependency($name, $default = null)
    {
        return array_key_exists($name, $this->dependencies) ? $this->dependencies[$name] : $default;
    }

    /**
     * Every configured view dependency.
     *
     * @return array
     */
    public function getDependencies()
    {
        foreach ($this->config['dependencies'] as $variable => $provider) {
            $this->dependencies[$variable] = call_user_func(
                [$this->container->get($provider[0]), $provider[1]]
            );
        }

        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class Custom view class name.
     * @throws ContainerException
     */
    public function get($path, array $data = [], $class = null)
    {
        list($namespace, $view) = $this->parsePath($path);

        //Some views have associated compiler
        $compiler = $this->compiler($namespace, $view, $engine, $filename);

        if (!empty($compiler) && !$compiler->isCompiled()) {
            //Pre-compile
            $compiler->compile();
        }

        return $this->container->construct(
            !empty($class) ? $class : $this->config['engines'][$engine]['view'],
            [
                'views'     => $this,
                'compiler'  => $compiler,
                'namespace' => $namespace,
                'view'      => $view,
                'filename'  => $filename,
                'data'      => $data
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function render($path, array $data = [])
    {
        return $this->get($path, $data)->render();
    }

    /**
     * Pre-compile desired view file.
     *
     * @param string $namespace
     * @param string $view
     * @return CompilerInterface|null
     */
    public function compile($namespace, $view)
    {
        $compiler = $this->compiler($namespace, $view);
        !empty($compiler) && $compiler->compile();

        return $compiler;
    }

    /**
     * Get list of view names associated with their View class.
     *
     * @param string $namespace
     * @return array
     * @throws ViewException
     */
    public function getViews($namespace)
    {
        if (!isset($this->namespaces[$namespace])) {
            throw new ViewException("Invalid view namespace '{$namespace}'.");
        }

        $result = [];
        foreach ($this->namespaces[$namespace] as $location) {
            $location = $this->files->normalizePath($location);
            foreach ($this->files->getFiles($location) as $filename) {
                $foundEngine = false;
                foreach ($this->config['engines'] as $engine => $options) {
                    if (in_array($this->files->extension($filename), $options['extensions'])) {
                        $foundEngine = $engine;
                        break;
                    }
                }

                if (empty($foundEngine)) {
                    //No engines found = not view
                    continue;
                }

                //View filename without extension
                $filename = substr($filename, 0, -1 - strlen($this->files->extension($filename)));
                $name = substr($filename, strlen($location) + strlen(FilesInterface::SEPARATOR));

                $result[$name] = $this->config['engines'][$foundEngine]['view'];
            }
        }

        return $result;
    }

    /**
     * Find view file specified by namespace and view name and associated engine id.
     *
     * @param string $namespace
     * @param string $view
     * @param string $engine Found engine id, reference.
     * @return string
     * @throws ViewException
     */
    public function getFilename($namespace, $view, &$engine = null)
    {
        if (!isset($this->namespaces[$namespace])) {
            throw new ViewException("Undefined view namespace '{$namespace}'.");
        }

        //This part better be cached one dat
        foreach ($this->namespaces[$namespace] as $directory) {
            foreach ($this->config['engines'] as $engine => $options) {
                foreach ($options['extensions'] as $extension) {
                    $candidate = $directory . FilesInterface::SEPARATOR . $view . '.' . $extension;

                    if ($this->files->exists($candidate)) {
                        return $this->files->normalizePath($candidate);
                    }
                }
            }
        }

        throw new ViewException("Unable to find view '{$view}' in namespace '{$namespace}'.");
    }

    /**
     * Get instance of compiler associated with specified namespace and view.
     *
     * @param string $namespace
     * @param string $view
     * @param string $engine   Selected engine name.
     * @param string $filename Reference to original view filename.
     * @return CompilerInterface|null
     * @throws ContainerException
     */
    private function compiler($namespace, $view, &$engine = null, &$filename = null)
    {
        $filename = $this->getFilename($namespace, $view, $engine);
        if (empty($this->config['engines'][$engine]['compiler'])) {
            return null;
        }

        //Building compiler with needed options
        return $this->container->construct(
            $this->config['engines'][$engine]['compiler'],
            [
                'views'     => $this,
                'config'    => $this->config['engines'][$engine],
                'namespace' => $namespace,
                'view'      => $view,
                'filename'  => $filename
            ] + $this->config['engines'][$engine]
        );
    }

    /**
     * Parse path to read namespace and view name.
     *
     * @param string $path
     * @return array
     */
    private function parsePath($path)
    {
        $namespace = static::DEFAULT_NAMESPACE;
        if (strpos($path, self::NS_SEPARATOR) !== false) {
            return explode(self::NS_SEPARATOR, $path);
        }

        return [$namespace, $path];
    }
}