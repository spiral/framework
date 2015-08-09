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
use Spiral\Core\ContainerInterface;
use Spiral\Files\FilesInterface;

abstract class Module extends Component implements ModuleInterface
{
    /**
     * Location of composer.json relatively to module class location.
     */
    const COMPOSER = '../composer.json';

    /**
     * Module bootstrapping. Custom code can be placed here.
     */
    public function bootstrap()
    {
        //Module specific
    }

    /**
     * Module definition should explain where module located, name, description and other meta
     * information about package, by default Definition can be created based on composer.json file.
     *
     * This method is static as it should be called without constructing module object.
     *
     * @param ContainerInterface $container
     * @return DefinitionInterface
     */
    public static function getDefinition(ContainerInterface $container)
    {
        /**
         * @var FilesInterface $files
         */
        $files = $container->get(FilesInterface::class);
        $directory = dirname((new \ReflectionClass(static::class))->getFileName());

        if (!$files->exists($composer = $directory . '/' . static::COMPOSER))
        {
            //Source directory is one level higher
            throw new ModuleException("Unable to locate composer.json file.");
        }

        $composer = json_decode($files->read($composer), true);

        //Let's use default definition
        return $container->get(Definition::class, [
            'class'        => static::class,
            'name'         => $composer['name'],
            'description'  => isset($composer['description']) ? $composer['description'] : '',
            'dependencies' => isset($composer['require']) ? array_keys($composer['require']) : ''
        ]);
    }

    /**
     * Module installer responsible for operations like copying resources, registering configs, view
     * namespaces and declaring that bootstrap() call is required.
     *
     * This method is static as it should be called without constructing module object.
     *
     * @param ContainerInterface  $container
     * @param DefinitionInterface $definition Module definition fetched or generated of composer file.
     * @return InstallerInterface
     */
    public static function getInstaller(ContainerInterface $container, DefinitionInterface $definition)
    {
        //Let's create default Installer
        return $container->get(Installer::class, [
            'moduleDirectory' => $definition->getLocation()
        ])

            ;
    }

    /**
     * Module installer responsible for operations like copying resources, registering configs, view
     * namespaces and declaring that bootstrap() call is required.
     *
     * This method is static as it should be called without constructing module object.
     *
     * @param ContainerInterface  $container
     * @param DefinitionInterface $definition Module definition fetched or generated of composer file.
     * @return InstallerInterface
     */
    public static function getInstaller(
        ContainerInterface $container,
        DefinitionInterface $definition
    );
}