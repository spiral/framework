<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\Container\ContainerException;
use Spiral\Core\Exceptions\Container\InjectionException;
use Spiral\Core\HippocampusInterface;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Files\FilesInterface;
use Spiral\Core\Singleton;
use Spiral\Tokenizer\TokenizerInterface;
use Spiral\Views\Exceptions\ViewException;

/**
 * Default ViewsInterface implementation with ability to change cache versions via external value
 * dependencies. ViewManager support multiple namespaces and namespaces associated with multiple
 * folders.
 */
class ViewManager extends Singleton implements ViewsInterface, InjectorInterface
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
     * List of already known views, associated with their filenames and engine id.
     *
     * @var array
     */
    private $associations = [];

    /**
     * List of already indexed classes to speed up View = view association.
     *
     * @var array
     */
    protected $classesCache = [];

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
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @invisible
     * @var TokenizerInterface
     */
    protected $tokenizer = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     * @param HippocampusInterface  $memory
     * @param FilesInterface        $files
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        ContainerInterface $container,
        HippocampusInterface $memory,
        FilesInterface $files
    )
    {
        $this->config = $configurator->getConfig(static::CONFIG);

        $this->container = $container;
        $this->memory = $memory;
        $this->files = $files;

        //So we don't need to crawl hard-drive every time
        $this->associations = $memory->loadData('views');

        //Namespaces can be edited in runtime
        $this->namespaces = $this->config['namespaces'];
    }

    /**
     * Tokenizer used to create association between view class and view path. Requested on demand.
     *
     * @param TokenizerInterface $tokenizer
     */
    public function setTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
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
        foreach ($this->config['dependencies'] as $variable => $provider)
        {
            $this->dependencies[$variable] = call_user_func(
                [$this->container->get($provider[0]), $provider[1]]
            );
        }

        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ContainerException
     */
    public function get($path, array $data = [])
    {
        list($namespace, $view) = $this->parsePath($path);

        return $this->container->get($this->association($namespace, $view), [
            'compiler'  => $this->compile($namespace, $view),
            'namespace' => $namespace,
            'view'      => $view,
            'data'      => $data
        ]);
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
     * @param bool   $reset Ignore compilation state and reset class association.
     * @return CompilerInterface
     */
    public function compile($namespace, $view, $reset = false)
    {
        $compiler = $this->compiler($namespace, $view);
        if (!empty($compiler) && (!$compiler->isCompiled() || $reset))
        {
            $compiler->compile();
        }

        if (empty($this->associations[$namespace][$view]) || $reset)
        {
            $this->association($namespace, $view, true);
        }

        return $compiler;
    }

    /**
     * Get list of view names associated with specified namespace. View file will be associated with
     * parent engine.
     *
     * @param string $namespace
     * @return array
     * @throws ViewException
     */
    public function getViews($namespace)
    {
        if (!isset($this->namespaces[$namespace]))
        {
            throw new ViewException("Invalid view namespace '{$namespace}'.");
        }

        $result = [];
        foreach ($this->namespaces[$namespace] as $location)
        {
            $location = $this->files->normalizePath($location);
            foreach ($this->files->getFiles($location) as $filename)
            {
                $foundEngine = false;
                foreach ($this->config['engines'] as $engine => $options)
                {
                    if (in_array($this->files->extension($filename), $options['extensions']))
                    {
                        $foundEngine = $engine;
                        break;
                    }
                }

                if (empty($foundEngine))
                {
                    //No engines found = not view
                    continue;
                }

                //View filename without extension
                $filename = substr($filename, 0, -1 - strlen($this->files->extension($filename)));
                $name = substr($filename, strlen($location) + strlen(FilesInterface::SEPARATOR));

                $result[$name] = $foundEngine;
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
        if (isset($this->associations[$namespace][$view]))
        {
            $engine = $this->associations[$namespace][$view][self::CACHE_ENGINE];

            return $this->associations[$namespace][$view][self::CACHE_FILENAME];
        }

        if (!isset($this->namespaces[$namespace]))
        {
            throw new ViewException("Undefined view namespace '{$namespace}'.");
        }

        //This part better be cached one dat
        foreach ($this->namespaces[$namespace] as $directory)
        {
            foreach ($this->config['engines'] as $engine => $options)
            {
                foreach ($options['extensions'] as $extension)
                {
                    $candidate = $directory . FilesInterface::SEPARATOR . $view . '.' . $extension;
                    if ($this->files->exists($candidate))
                    {
                        return $this->files->normalizePath($candidate);
                    }
                }
            }
        }

        throw new ViewException("Unable to find view '{$view}' in namespace '{$namespace}'.");
    }

    /**
     * {@inheritdoc}
     *
     * Create specific View class with associated compiler.
     */
    public function createInjection(\ReflectionClass $class, \ReflectionParameter $parameter)
    {
        if (empty($class->getConstant('VIEW')))
        {
            throw new InjectionException("Unable to construct View, no path clue provided.");
        }

        $namespace = static::DEFAULT_NAMESPACE;
        $view = $class->getConstant('VIEW');

        if (strpos($view, self::NS_SEPARATOR) !== false)
        {
            list($namespace, $view) = explode(self::NS_SEPARATOR, $view);
        }

        return $this->container->get($class, [
            'compiler' => $this->compiler($namespace, $view)
        ]);
    }

    /**
     * Ensure and return name of class associated with specific view namespace and name. Can be
     * re-setted by providing reset flag with true value.
     *
     * @param string $namespace
     * @param string $view
     * @param bool   $reset
     * @return string
     */
    private function association($namespace, $view, $reset = false)
    {
        if (isset($this->associations[$namespace][$view]) && !$reset)
        {
            return $this->associations[$namespace][$view]  [self::CACHE_CLASS];
        }

        $filename = $this->getFilename($namespace, $view, $engine);
        $this->associations[$namespace][$view] = [
            self::CACHE_CLASS    => $class = $this->getClass($engine, $namespace, $view),
            self::CACHE_ENGINE   => $engine,
            self::CACHE_FILENAME => $filename
        ];

        $this->memory->saveData('views', $this->associations);

        return $class;
    }

    /**
     * Get instance of compiler associated with specified namespace and view.
     *
     * @param string $namespace
     * @param string $view
     * @return CompilerInterface|null
     * @throws ContainerException
     */
    private function compiler($namespace, $view)
    {
        $filename = $this->getFilename($namespace, $view, $engine);

        if (empty($this->config['engines'][$engine]['compiler']))
        {
            return null;
        }

        return $this->container->get($this->config['engines'][$engine]['compiler'], [
            'views'     => $this,
            'config'    => $this->config['engines'][$engine],
            'namespace' => $namespace,
            'view'      => $view,
            'filename'  => $filename
        ]);
    }

    /**
     * Get associated view class or return default engine view.
     *
     * @param string $engine
     * @param string $namespace
     * @param string $view
     * @return string
     */
    private function getClass($engine, $namespace, $view)
    {
        if (!$this->config['viewAssociations'])
        {
            //Always use default class
            return $this->config['engines'][$engine]['view'];
        }

        $path = $view;
        if ($namespace != self::DEFAULT_NAMESPACE)
        {
            $path = $namespace . self::NS_SEPARATOR . $view;
        }

        if (empty($this->classesCache))
        {
            $this->classesCache = $this->tokenizer()->getClasses(View::class);
        }

        foreach ($this->classesCache as $class => $definition)
        {
            if ($definition['abstract'])
            {
                continue;
            }

            if ($class::VIEW == $path)
            {
                //We found responsible class
                return $class;
            }
        }

        return $this->config['engines'][$engine]['view'];
    }

    /**
     * Get associated tokenizer instance.
     *
     * @return TokenizerInterface
     */
    private function tokenizer()
    {
        if (!empty($this->tokenizer))
        {
            return $this->tokenizer;
        }

        return $this->tokenizer = $this->container->get(TokenizerInterface::class);
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
        if (strpos($path, self::NS_SEPARATOR) !== false)
        {
            return explode(self::NS_SEPARATOR, $path);
        }

        return [$namespace, $path];
    }
}