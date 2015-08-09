<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules\Prototypes;

use Spiral\Core\ContainerInterface;
use Spiral\Files\FilesInterface;
use Spiral\Modules\DefinitionInterface;
use Spiral\Modules\Exceptions\ModuleException;
use Spiral\Modules\ModuleManager;

/**
 * Default implementation of DefinitionInterface, uses composer.json file to read information about
 * module name, dependencies and etc. Module must extend Module prototype in order to work with
 * this Definition (createInstaller method is required).
 *
 * Module location (root) directory will be resolved as second parent folder of Module class.
 *
 * Example:
 * Module class:    vendor/package/scr/Namespace/Class.php
 * Module location: vendor/package/scr
 */
class Definition implements DefinitionInterface
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * @invisible
     * @var Installer
     */
    private $installer = null;

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @invisible
     * @var ModuleManager
     */
    protected $modules = null;

    /**
     * @param FilesInterface $file
     * @param ModuleManager  $modules
     * @param string         $class
     * @param string         $name
     * @param string         $description
     * @param array          $dependencies
     */
    public function __construct(
        FilesInterface $file,
        ModuleManager $modules,
        $class,
        $name,
        $description = '',
        $dependencies = []
    ) {
        $this->files = $file;
        $this->modules = $modules;

        $this->class = $class;
        $this->name = $name;
        $this->description = $description;
        $this->dependencies = $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation()
    {
        return $this->files->normalizePath(dirname(dirname(
            (new \ReflectionClass($this->class))->getFileName()
        )));
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        $totalSize = 0;
        foreach ($this->files->getFiles($this->getLocation()) as $filename) {
            $totalSize += filesize($filename);
        }

        return $totalSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return (bool)$this->modules->hasModule($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        if (!empty($this->installer)) {
            return $this->installer;
        }

        return $this->installer = call_user_func([$this->class, 'getInstaller'], $this);
    }

    /**
     * Create Definition using local composer.json file.
     *
     * @param ContainerInterface $container
     * @param string             $class    Module class name.
     * @param string             $composer Relative location of composer file.
     * @return DefinitionInterface
     * @throws ModuleException
     */
    public static function fromComposer(ContainerInterface $container, $class, $composer)
    {
        /**
         * We will fill name property little bit later.
         *
         * @var static $definition
         */
        $definition = $container->get(static::class, [
            'class' => $class,
            'name'  => null
        ]);

        //Composer file
        $composer = $definition->getLocation() . FilesInterface::SEPARATOR . $composer;
        if (!$definition->files->exists($composer)) {
            throw new ModuleException("Unable to locate composer.json file.");
        }

        $composer = json_decode($definition->files->read($composer), true);

        $definition->name = $composer['name'];

        if (!empty($composer['description'])) {
            $definition->description = $composer['description'];
        }

        if (!empty($composer['require'])) {
            $definition->dependencies = array_keys($composer['require']);
        }

        return $definition;
    }
}