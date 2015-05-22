<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;

class ViewManager extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait,
        Component\ConfigurableTrait,
        Component\LoggerTrait,
        Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Default view namespace. View component can support as many namespaces and user want, to
     * specify names use render(namespace:view) syntax.
     */
    const DEFAULT_NAMESPACE = 'default';

    /**
     * Extension used to represent cached view files.
     */
    const CACHE_EXTENSION = 'php';

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * FileManager component.
     *
     * @invisible
     * @var FileManager
     */
    protected $file = null;

    /**
     * Registered view namespaces. Every namespace can include multiple search directories. Search
     * directory may have key which will be treated as namespace directory origin, this allows user
     * or template to include view from specified location, even if there is multiple directories under
     * view.
     *
     * @var array
     */
    protected $namespaces = array();

    /**
     * Variables for pre-processing and cache definition.
     *
     * @var array
     */
    protected $staticVariables = array();

    /**
     * Constructing view component and initiating view namespaces, namespaces are used to find view
     * file destination and switch templates from one module to another.
     *
     * @param Container     $container
     * @param CoreInterface $core
     * @param FileManager   $file
     */
    public function __construct(Container $container, CoreInterface $core, FileManager $file)
    {
        $this->container = $container;
        $this->file = $file;
        $this->config = $core->loadConfig('views');

        //Mounting namespaces from config and external modules
        $this->namespaces = $this->config['namespaces'];
    }

    /**
     * Current view cache directory.
     *
     * @return string
     */
    public function cacheDirectory()
    {
        return $this->config['caching']['directory'];
    }

    /**
     * All registered and available view namespaces.
     *
     * @return array|null
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Update view namespaces.
     *
     * @param array $namespaces
     * @return array
     */
    public function setNamespaces(array $namespaces)
    {
        return $this->namespaces = $namespaces;
    }

    /**
     * Add view namespace directory.
     *
     * @param string $namespace
     * @param string $directory
     * @return static
     */
    public function addNamespace($namespace, $directory)
    {
        if (!isset($this->namespaces[$namespace]))
        {
            $this->namespaces[$namespace] = array();
        }

        $this->namespaces[$namespace][] = $directory;

        return $this;
    }

    /**
     * Variables which will be applied on view caching and view processing stages, different variable
     * value will create different cache version. Usage example can be: layout redefinition, user
     * logged state and etc. You should never use this function for client or dynamic data.
     *
     * @param string $name
     * @param string $value If skipped current value or null will be returned.
     * @return string
     */
    public function staticVariable($name, $value = null)
    {
        if (func_num_args() == 1)
        {
            return isset($this->staticVariables[$name]) ? $this->staticVariables[$name] : null;
        }

        return $this->staticVariables[$name] = $value;
    }

    /**
     * Cached filename depends only on view name and provided set of "staticVariables", changing this
     * set system can cache some view content on file system level. For example view component can
     * set language variable, which will be rendering another view every time language changed and
     * allow to cache translated texts.
     *
     * @param string $namespace View namespace.
     * @param string $view      View filename, without php included.
     * @return string
     */
    public function cachedFilename($namespace, $view)
    {
        foreach ($this->config['staticVariables'] as $variable => $provider)
        {
            $this->staticVariables[$variable] = call_user_func(array(
                $this->container->get($provider[0]),
                $provider[1]
            ));
        }

        $postfix = '-' . hash('crc32b', join(',', $this->staticVariables)) . '.' . self::CACHE_EXTENSION;

        return $this->cacheDirectory() . '/'
        . $namespace . '-' . trim(str_replace(array('\\', '/'), '-', $view), '-')
        . $postfix;
    }

    /**
     * Searching for view in namespaces. Namespace specifies set of view files joined by module or
     * application folder or etc. View name is relative file name (starting with namespace folder).
     *
     * @param string $namespace View namespace.
     * @param string $view      View filename, without .php included.
     * @param string $engine    Name of used engine.
     * @return string
     * @throws ViewException
     */
    public function findView($namespace, $view, &$engine = null)
    {
        if (!isset($this->namespaces[$namespace]))
        {
            throw new ViewException("Undefined view namespace '{$namespace}'.");
        }

        /**
         * This section can be potentially cached in view runtime data to optimize
         * view detection process, it's not required at this moment... but possible.
         */
        foreach ($this->namespaces[$namespace] as $directory)
        {
            foreach ($this->config['engines'] as $engine => $options)
            {
                foreach ($options['extensions'] as $extension)
                {
                    if ($this->file->exists($directory . '/' . $view . '.' . $extension))
                    {
                        return $this->file->normalizePath($directory . '/' . $view . '.' . $extension);
                    }
                }
            }
        }

        throw new ViewException("Unable to find view '{$view}' in namespace '{$namespace}'.");
    }

    /**
     * Check if compiled view cache expired and has to be re-rendered. You can disable view cache
     * by altering view config (this will slow your application dramatically but will simplyfy
     * development).
     *
     * @param string $viewFilename
     * @param string $cachedFilename
     * @return bool
     */
    protected function isExpired($viewFilename, $cachedFilename)
    {
        if (!$this->config['caching']['enabled'])
        {
            //Aways invalidate
            return true;
        }

        if (!$this->file->exists($cachedFilename))
        {
            return true;
        }

        return $this->file->timeUpdated($cachedFilename) < $this->file->timeUpdated($viewFilename);
    }

    /**
     * Return cached or not cached version of view. Automatically apply view processors to source.
     *
     * @param string $namespace  View namespace.
     * @param string $view       View filename, without php included.
     * @param bool   $compile    If true, view source will be processed using view processors before
     *                           saving to cache.
     * @param bool   $resetCache Force cache reset. Cache can be also disabled in view config.
     * @param string $engine     Name of used engine.
     * @return string
     * @throws ViewException
     */
    public function getFilename(
        $namespace,
        $view,
        $compile = true,
        $resetCache = false,
        &$engine = null
    )
    {
        $viewFilename = $this->findView($namespace, $view, $engine);

        //Pre-compilation is possible only when engine defined compiler
        if ($compile && !empty($this->config['engines'][$engine]['compiler']))
        {
            //Cached filename
            $cacheFilename = $this->cachedFilename($namespace, $view);

            if ($resetCache || $this->isExpired($viewFilename, $cacheFilename))
            {
                $compiled = $this->compile($engine, $namespace, $view, $viewFilename, $cacheFilename);

                //Saving compilation result to filename
                $this->file->write($cacheFilename, $compiled, FileManager::RUNTIME, true);
            }

            return $cacheFilename;
        }

        return $viewFilename;
    }

    /**
     * Generate view cache using defined compiler.
     *
     * @param string $engine
     * @param string $namespace View namespace.
     * @param string $view      View filename, without php included.
     * @param string $input     Input view filename.
     * @param string $output    Output filename.,
     * @return bool|string
     */
    protected function compile($engine, $namespace, $view, $input, $output)
    {
        /**
         * @var CompilerInterface $compiler
         */
        $compiler = $this->container->get($this->config['engines'][$engine]['compiler'], array(
                'manager'   => $this,
                'source'    => $this->file->read($input),
                'namespace' => $namespace,
                'view'      => $view,
                'input'     => $input,
                'output'    => $output,
            ) + $this->config['engines'][$engine]);

        return $compiler->compile();
    }

    /**
     * Get instance of View class binded to specified view filename. View file will can be selected
     * from specified namespace, or default namespace if not specified.
     *
     * Every view file will be pro-processed using view processors (also defined in view config) before
     * rendering, result of pre-processing will be stored in names cache file to speed-up future
     * renderings.
     *
     * Example or view names:
     * home                     - render home view from default namespace
     * namespace:home           - render home view from specified namespace
     *
     * @param string $view View name without .php extension, can include namespace prefix separated
     *                     by : symbol.
     * @param array  $data Array or view data, will be exported as local view variables, not available
     *                     in view processors.
     * @return ViewInterface
     */
    public function get($view, array $data = array())
    {
        $namespace = self::DEFAULT_NAMESPACE;
        if (strpos($view, ':'))
        {
            list($namespace, $view) = explode(':', $view);
        }

        //Compiled view source
        $filename = $this->getFilename($namespace, $view, true, false, $engine);

        //View representer
        $renderer = $this->config['engines'][$engine]['view'];

        return new $renderer($this, $filename, $namespace, $view, $data);
    }

    /**
     * Perform view file rendering. View file will can be selected from specified namespace, or
     * default namespace if not specified.
     *
     * View data has to be associated array and will be exported using extract() function and set of
     * local view variables, here variable name will be identical to array key.
     *
     * Every view file will be pro-processed using view processors (also defined in view config) before
     * rendering, result of pre-processing will be stored in names cache file to speed-up future
     * renderings.
     *
     * Example or view names:
     * home                     - render home view from default namespace
     * namespace:home           - render home view from specified namespace
     *
     * @param string $view View name without .php extension, can include namespace prefix separated
     *                     by : symbol.
     * @param array  $data Array or view data, will be exported as local view variables, not available
     *                     in view processors.
     * @return string
     */
    public function render($view, array $data = array())
    {
        return $this->get($view, $data)->render();
    }
}