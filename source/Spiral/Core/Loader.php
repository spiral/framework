<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core;

use Spiral\Core\Container\SingletonInterface;

/**
 * Can speed up class loading in some conditions. Used by profiler to show all loadead classes.
 *
 * Implementation work
 */
class Loader extends Component implements SingletonInterface
{
    /**
     * Default memory segment.
     */
    const MEMORY = 'loadmap';

    /**
     * Loader memory segment.
     *
     * @var string
     */
    private $name = '';

    /**
     * List of classes loaded while this working session.
     *
     * @var array
     */
    private $classes = [];

    /**
     * Association between class and it's location.
     *
     * @var array
     */
    private $loadmap = [];

    /**
     * Is SPL methods handled.
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * @invisible
     * @var MemoryInterface
     */
    protected $memory = null;

    /**
     * Loader will automatically handle SPL autoload functions to start caching loadmap.
     *
     * @param MemoryInterface $memory
     * @param bool            $enable Automatically enable.
     * @param string          $name
     */
    public function __construct(
        MemoryInterface $memory,
        bool $enable = true,
        string $name = self::MEMORY
    ) {
        $this->memory = $memory;
        $this->name = $name;

        if ($enable) {
            $this->enable();
        }
    }

    /**
     * Check if loader is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Handle SPL auto location, will go to top of spl chain.
     *
     * @return $this|self
     */
    public function enable(): Loader
    {
        if ($this->enabled) {
            return $this;
        }

        spl_autoload_register([$this, 'loadClass'], true, true);

        $this->enabled = true;
        $this->loadmap = (array)$this->memory->loadData(static::MEMORY);

        return $this;
    }

    /**
     * Stop handling SPL calls.
     *
     * @return $this|self
     */
    public function disable(): Loader
    {
        if (!$this->enabled) {
            return $this;
        }

        spl_autoload_unregister([$this, 'loadClass']);

        $this->memory->saveData($this->name, $this->loadmap);
        $this->enabled = false;

        return $this;
    }

    /**
     * Re-enable autoload to push it up into food chain.
     */
    public function reset()
    {
        return $this->disable()->enable();
    }

    /**
     * Try to load class declaration from memory or delegate it to other auto-loaders.
     *
     * @param string $class Class name with namespace included.
     */
    public function loadClass(string $class)
    {
        if (isset($this->loadmap[$class])) {
            try {
                //We already know route to class declaration
                include_once($this->classes[$class] = $this->loadmap[$class]);

                return;
            } catch (\Throwable $e) {
                //Delegating to external loaders
            }
        }

        $this->classes[$class] = null;
        $this->loadExternal($class);
    }

    /**
     * All loaded classes.
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Check if desired class exists in loadmap.
     *
     * @param string $class
     *
     * @return bool
     */
    public function isKnown(string $class): bool
    {
        return array_key_exists($class, $this->classes);
    }

    /**
     * Destroy loader.
     */
    public function __destruct()
    {
        $this->disable();
    }

    /**
     * Try to load class using external auto-loaders.
     *
     * @param string $class
     */
    protected function loadExternal(string $class)
    {
        foreach (spl_autoload_functions() as $function) {
            if (is_array($function) && get_class($function[0]) == self::class) {
                //Found ourselves
                continue;
            }

            //Call inner loader
            call_user_func($function, $class);

            if (
                class_exists($class, false)
                || interface_exists($class, false)
                || trait_exists($class, false)
            ) {
                //We need reflection to find class location
                $reflector = new \ReflectionClass($class);

                try {
                    $filename = $reflector->getFileName();
                    $filename = rtrim(
                        str_replace(['\\', '//'], '/', $filename),
                        '/'
                    );

                    if (file_exists($filename)) {
                        $this->loadmap[$class] = $this->classes[$class] = $filename;
                    }
                } catch (\Throwable $e) {
                    //Get filename for classes located in PHARs might break reflection
                }

                break;
            }
        }
    }
}
