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
use Spiral\Core\Core;

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
    const SINGLETON = 'view';

    /**
     * Following extension will be used for all view files.
     */
    const EXTENSION = '.php';

    /**
     * View does not going to check if view file got changed compared to cached view filename. This
     * can applied to production environment statically build view files.
     *
     * Attention! You WILL have to generate static view cache if using this option.
     *
     * Use spiral.cli spiral:core viewCache --generate
     */
    const HARD_CACHE = 2;

    /**
     * Default view namespace. View component can support as many namespaces and user want, to
     * specify names use render(namespace:view) syntax.
     */
    protected $defaultNamespace = 'default';

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
     * View processors. Processors used to pre-process view source and save it to cache, in normal
     * operation mode processors will be called only once and never during user request.
     *
     * @var ProcessorInterface[]
     */
    protected $processors = array();

    /**
     * Variables for pre-processing and cache definition.
     *
     * @var array
     */
    protected $staticVariables = array();

    /**
     * FileManager component.
     *
     * @invisible
     * @var FileManager
     */
    protected $file = null;

    /**
     * Constructing view component and initiating view namespaces, namespaces are used to find view
     * file destination and switch templates from one module to another.
     *
     * @param Core        $core
     * @param FileManager $file
     */
    public function __construct(Core $core, FileManager $file)
    {
        $this->file = $file;
        $this->config = $core->loadConfig('views');

        //Mounting namespaces from config and external modules
        $this->namespaces = $this->config['namespaces'];
    }

    /**
     * Default namespace value.
     *
     * @return string
     */
    public function defaultNamespace()
    {
        return $this->defaultNamespace;
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
     * Getting view processor by name, processor will be loaded and configured automatically. Processors
     * are created only for pre-processing view source to create static cache, this means you should't
     * expect too high performance and optimizations inside, due it's more important to have good
     * functionality and reliable results.
     *
     * You should never user view component in production with disabled cache, this will slow down
     * your website dramatically.
     *
     * @param string $name
     * @return ProcessorInterface
     */
    public function getProcessor($name)
    {
        if (isset($this->processors[$name]))
        {
            return $this->processors[$name];
        }

        $config = $this->config['processors'][$name];

        return $this->processors[$name] = Container::get(
            $config['class'],
            array(
                'options' => $config,
                'view'    => $this
            )
        );
    }

    /**
     * Names of currently registered view processors in their priority order.
     *
     * @return array
     */
    public function getProcessors()
    {
        return array_keys($this->config['processors']);
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
                Container::get($provider[0]),
                $provider[1]
            ));
        }

        $postfix = '-' . hash('crc32b', join(',', $this->staticVariables)) . static::EXTENSION;

        return $this->cacheDirectory() . '/'
        . $namespace . '-' . trim(str_replace(array('\\', '/'), '-', $view), '-') . $postfix;
    }

    /**
     * Searching for view in namespaces. Namespace specifies set of view files joined by module or
     * application folder or etc. View name is relative file name (starting with namespace folder).
     *
     * @param string $namespace View namespace.
     * @param string $view      View filename, without .php included.
     * @return string
     * @throws ViewException
     */
    public function findView($namespace, $view)
    {
        if (!isset($this->namespaces[$namespace]))
        {
            throw new ViewException("Undefined view namespace '{$namespace}'.");
        }

        foreach ($this->namespaces[$namespace] as $directory)
        {
            if ($this->file->exists($directory . '/' . $view . static::EXTENSION))
            {
                return $this->file->normalizePath($directory . '/' . $view . static::EXTENSION);
            }
        }

        throw new ViewException("Unable to find view '{$view}' in namespace '{$namespace}'.");
    }

    /**
     * Check if cache view file expired, by default there is soft and hard cache techniques, soft
     * will compare filetimes and hard will always return true (use on production with static view
     * cache).
     *
     * @param string $filename  View cache filename.
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @return bool
     * @throws ViewException
     */
    protected function isExpired($filename, $namespace, $view)
    {
        if ($this->config['caching']['enabled'] === self::HARD_CACHE)
        {
            return false;
        }

        if (!$this->config['caching']['enabled'])
        {
            //Aways invalidate
            return true;
        }

        if (!$this->file->exists($filename))
        {
            return true;
        }

        return $this->file->timeUpdated($filename) < $this->file->timeUpdated(
            $this->findView($namespace, $view)
        );
    }

    /**
     * Returns cached or not cached version of view. Automatically apply view processors to source.
     *
     * @param string $namespace  View namespace.
     * @param string $view       View filename, without php included.
     * @param bool   $process    If true, view source will be processed using view processors before
     *                           saving to cache.
     * @param bool   $resetCache Force cache reset. Cache can be also disabled in view config.
     * @return string
     * @throws ViewException
     */
    public function getFilename($namespace, $view, $process = true, $resetCache = false)
    {
        if ($process)
        {
            //Cached filename
            $filename = $this->cachedFilename($namespace, $view);

            if ($resetCache || $this->isExpired($filename, $namespace, $view))
            {
                //Getting source from original filename
                $source = $this->file->read($this->getFilename($namespace, $view, false));

                foreach (array_keys($this->config['processors']) as $processor)
                {
                    benchmark('view::' . $processor, $namespace . ':' . $view);

                    //Compiling
                    $source = $this->getProcessor($processor)->processSource(
                        $source,
                        $view,
                        $namespace,
                        $filename
                    );

                    benchmark('view::' . $processor, $namespace . ':' . $view);
                }

                //Saving compilation result to cache
                $this->file->write($filename, $source, FileManager::RUNTIME, true);
            }

            return $filename;
        }

        return $this->findView($namespace, $view);
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
     * @return View
     */
    public function get($view, array $data = array())
    {
        $namespace = $this->defaultNamespace;
        if (strpos($view, ':'))
        {
            list($namespace, $view) = explode(':', $view);
        }

        return View::make(array(
            'namespace' => $namespace,
            'view'      => $view,
            'filename'  => $this->getFilename($namespace, $view),
            'data'      => $data
        ));
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