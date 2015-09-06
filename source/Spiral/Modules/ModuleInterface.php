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

/**
 * Describes ability to be located by ModuleManager and installed with custom configs, resources
 * and e tc.
 */
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
}