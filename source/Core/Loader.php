<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component\EventsTrait;
use Spiral\Core\Component\SingletonTrait;

class Loader extends Component
{
    /**
     * Required traits.
     */
    use SingletonTrait, EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * RuntimeCacheInterface instance.
     *
     * @var RuntimeCacheInterface
     */
    protected $runtime = null;

    /**
     * List of classes loading during this working session.
     *
     * @var array
     */
    protected $classes = array();

    /**
     * Name of loadmap file to use.
     *
     * @var string
     */
    protected $name = 'loadmap';

    /**
     * Cached class locations, used to speed up classes loading and resolving names by namespace and
     * postfix. In any scenario loadmap can significantly speedup application, due there is no need
     * to ping filesystem anymore.
     *
     * @var array
     */
    protected $loadmap = array();

    /**
     * Loader state.
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Loader will automatically handle SPL autoload functions to start caching loadmap. In future
     * loadmap can be used to pre-load all classes via one single file.
     *
     * @param RuntimeCacheInterface $runtime
     */
    public function __construct(RuntimeCacheInterface $runtime)
    {
        $this->runtime = $runtime;
        $this->enable();
    }

    /**
     * Performs auto-loading and core components initializations. All found classes will be saved
     * into loadmap and fetched directly from it next call load request (without performing file lookup).
     *
     * @return static
     * @throws CoreException
     */
    public function enable()
    {
        if (!$this->enabled)
        {
            if ((!$this->loadmap = $this->runtime->loadData($this->name)) || !is_array($this->loadmap))
            {
                $this->loadmap = array();
            }

            spl_autoload_register(array($this, 'loadClass'), true, true);
            $this->enabled = true;
        }

        return $this;
    }

    /**
     * Disable autoloading classes via \spiral\Loader.
     *
     * @return static
     */
    public function disable()
    {
        $this->enabled && spl_autoload_unregister(array($this, 'loadClass'));
        $this->enabled = false;

        return $this;
    }

    /**
     * Re-enabling autoloader to push up. This operation is required if some other class loader
     * trying to handle autoload function.
     *
     * For example - composer.
     */
    public function reset()
    {
        $this->disable()->enable();
    }

    /**
     * Update loadmap cache name. Can be used to separate environment loadmaps.
     *
     * @param string $name
     */
    public function setName($name)
    {
        if ($this->name != $name)
        {
            if ((!$this->loadmap = $this->runtime->loadData($name)) || !is_array($this->loadmap))
            {
                $this->loadmap = array();
            }
        }

        $this->name = $name;
    }

    /**
     * Find class declaration and load it.
     *
     * @param string $class Class name with namespace included.
     * @return void
     * @throws CoreException
     */
    public function loadClass($class)
    {
        if (isset($this->loadmap[$class]))
        {
            try
            {
                //We already know route to class declaration
                include_once($this->classes[$class] = $this->loadmap[$class]);
            }
            catch (\ErrorException $exception)
            {
                //File was replaced or removed
                unset($this->loadmap[$class]);
                $this->runtime->saveData($this->name, $this->loadmap, null, true);

                //Trying to update route to class
                $this->loadClass($class);
            }

            return;
        }

        foreach (spl_autoload_functions() as $function)
        {
            if ($function instanceof \Closure || $function[0] != $this)
            {
                call_user_func($function, $class);

                //Class was successfully found by external loader
                if (
                    class_exists($class, false)
                    || interface_exists($class, false)
                    || trait_exists($class, false)
                )
                {
                    //External loader will not provide us any information about class location, let's
                    //get it via Reflection
                    $reflector = new \ReflectionClass($class);

                    try
                    {
                        $filename = FileManager::getInstance()->normalizePath($reflector->getFileName());
                    }
                    catch (\ErrorException $exception)
                    {
                    }

                    if (isset($filename) && file_exists($filename))
                    {
                        $this->loadmap[$class] = $this->classes[$class] = $filename;
                        $this->runtime->saveData($this->name, $this->loadmap, null, true);
                    }

                    return;
                }
            }
        }

        $this->event('notFound', compact('class'));
    }

    /**
     * All loaded classes.
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Current loadmap state.
     *
     * @return array
     */
    public function getLoadmap()
    {
        return $this->loadmap;
    }

    /**
     * Will force loadmap content or reset it.
     *
     * @param array $loadmap
     */
    public function setLoadmap($loadmap = array())
    {
        $this->loadmap = $loadmap;

        $this->runtime->saveData($this->name, $this->loadmap, null, true);
    }

    /**
     * Check if desired class exists in loadmap.
     *
     * @param string $class Class name.
     * @return array
     */
    public function isKnown($class)
    {
        return isset($this->classes[$class]);
    }
}