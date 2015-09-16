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

/**
 * Default implementation of DefinitionInterface, uses composer.json file to read information about
 * module name, dependencies and etc. Module must extend Module prototype in order to work with
 * this Definition (createInstaller method is required).
 *
 *  Module location (root) directory will be resolved as composer.json location.
 *
 * Example:
 * Module class:    vendor/package/scr/Namespace/Class.php
 * Module location: vendor/package
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
     * Root module location.
     *
     * @var string
     */
    private $location = '';

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
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param ContainerInterface $container
     * @param FilesInterface     $file
     * @param string             $class
     * @param string             $name
     * @param string             $description
     * @param array              $dependencies
     */
    public function __construct(
        ContainerInterface $container,
        FilesInterface $file,
        $class,
        $name,
        $description = '',
        $dependencies = []
    ) {
        $this->container = $container;
        $this->files = $file;

        $this->class = $class;
        $this->name = $name;
        $this->description = $description;
        $this->dependencies = $dependencies;

        //By default we will user module class directory
        $this->location = $this->files->normalizePath(
            dirname((new \ReflectionClass($this->class))->getFileName()),
            true
        );
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
        return $this->location;
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
    public function getInstaller()
    {
        if (!empty($this->installer)) {
            return $this->installer;
        }

        return $this->installer = call_user_func(
            [$this->class, 'getInstaller'], $this->container, $this
        );
    }

    /**
     * Create Definition using local composer.json file. Root directory will be resolved as location
     * of composer.json
     *
     * @param ContainerInterface $container
     * @param string             $class    Module class name.
     * @param string             $composer Name of composer file.
     * @return DefinitionInterface
     * @throws ModuleException
     */
    public static function fromComposer(
        ContainerInterface $container,
        $class,
        $composer = 'composer.json'
    ) {
        /**
         * We will fill name property little bit later.
         *
         * @var static $definition
         */
        $definition = $container->construct(static::class, [
            'class' => $class,
            'name'  => null
        ]);

        //Let's look for composer file
        $location = $definition->location;
        while (!$definition->files->exists($location . FilesInterface::SEPARATOR . $composer)) {
            $location = dirname($location);
            if (empty(trim($location, '\\/.'))) {
                throw new ModuleException("Unable to locate composer.json file.");
            }
        }

        //We found root location
        $definition->location = $definition->files->normalizePath($location);
        $composer = json_decode(
            $definition->files->read($location . FilesInterface::SEPARATOR . $composer),
            true
        );

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