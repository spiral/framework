<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

use Spiral\Core\ContainerInterface;

interface ModuleInterface
{
    /**
     * Module bootstrapping. Custom code can be placed here.
     */
    public function bootstrap();

    /**
     * Module definition should explain where module located, name, description and other meta
     * information about package, by default Definition can be created based on composer.json file.
     *
     * This method is static as it should be called without constructing module object.
     *
     * @param ContainerInterface $container
     * @return DefinitionInterface
     */
    public static function getDefinition(ContainerInterface $container);

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
    public static function getInstaller(ContainerInterface $container, DefinitionInterface $definition);
}