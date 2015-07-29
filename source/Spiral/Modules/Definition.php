<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

use Spiral\Core\Component;
use Spiral\Files\FilesInterface;

class Definition extends Component
{
    /**
     * FileManager component.
     *
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * ModuleManager component.
     *
     * @invisible
     * @var ModuleManager
     */
    protected $modules = null;

    /**
     * Module name, can contain version or other short description, like "Spiral Profiler v1.2.0"
     * Can be fetched from composer.json, "name" field.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Module description, can be automatically fetched from composer.json file, "description" field.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Module class name used to fetch installer and register module in configuration.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Module installer class, installer contains set of operation to copy, update or merge files,
     * also installer declared if any view namespaces, bindings or component bootstrap function should
     * be registered.
     *
     * @invisible
     * @var Installer
     */
    protected $installer = null;

    /**
     * Module dependencies (composer libraries).
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * Module definition should explain where module located, name, description and other meta
     * information about package, by default Definition can be created based on composer.json file.
     *
     * @param FileManager   $file
     * @param ModuleManager $modules
     * @param string        $class
     * @param string        $name
     * @param string        $description
     * @param array         $dependencies
     */
    public function __construct(
        FileManager $file,
        ModuleManager $modules,
        $class,
        $name,
        $description = '',
        $dependencies = []
    )
    {
        $this->files = $file;
        $this->modules = $modules;

        $this->class = $class;
        $this->name = $name;
        $this->description = $description;
        $this->dependencies = $dependencies;
    }

    /**
     * Module name, can contain version or other short description, like "Spiral Profiler v1.2.0"
     * Can be fetched from composer.json, "name" field.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Module description, can be automatically fetched from composer.json file, "description" field.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Module class name used to fetch installer and register module in configuration.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Location of module class, all module files should be located in same directory.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->files->normalizePath(dirname(
            (new \ReflectionClass($this->class))->getFileName()
        ));
    }

    /**
     * Total module size in bytes (will calculate all module files including resources, views and
     * module class itself).
     *
     * @return int
     */
    public function getSize()
    {
        $totalSize = 0;
        foreach ($this->files->getFiles($this->getLocation()) as $filename)
        {
            $totalSize += filesize($filename);
        }

        return $totalSize;
    }

    /**
     * Get list of module dependencies.
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Check if module class already registered in modules list (modules config file).
     *
     * @return array|bool
     */
    public function isInstalled()
    {
        return (bool)$this->modules->hasModule($this->getName());
    }

    /**
     * Module installer responsible for operations like copying resources, registering configs, view
     * namespaces and declaring that bootstrap() call is required. Installer declaration should be
     * located in Module::getInstaller() method.
     *
     * @return Installer
     */
    public function getInstaller()
    {
        if (!empty($this->installer))
        {
            return $this->installer;
        }

        return $this->installer = call_user_func([$this->class, 'getInstaller'], $this);
    }
}