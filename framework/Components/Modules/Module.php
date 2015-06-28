<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Modules;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;

abstract class Module extends Component implements ModuleInterface
{
    /**
     * Module bootstrapping. Custom code can be placed here.
     */
    public function bootstrap()
    {
    }

    /**
     * Module definition should explain where module located, name, description and other meta
     * information about package, by default Definition can be created based on composer.json file.
     *
     * This method is static as it should be called without constructing module object.
     *
     * @return Definition
     */
    public static function getDefinition()
    {
        $file = FileManager::getInstance();

        $moduleDirectory = dirname((new \ReflectionClass(get_called_class()))->getFileName());
        $composer = $moduleDirectory . '/composer.json';

        if (!$file->exists($composer))
        {
            if (!$file->exists($composer = basename($moduleDirectory) . '/composer.json'))
            {
                //Source directory is one level higher
                throw new ModuleException("Unable to locate composer.json file.");
            }
        }

        $composer = json_decode($file->read($composer), true);

        return Definition::make(array(
            'class'        => get_called_class(),
            'name'         => $composer['name'],
            'description'  => isset($composer['description']) ? $composer['description'] : '',
            'dependencies' => isset($composer['require']) ? array_keys($composer['require']) : ''
        ));
    }

    /**
     * Module installer responsible for operations like copying resources, registering configs, view
     * namespaces and declaring that bootstrap() call is required.
     * This method is static as it should be called without constructing module object.
     *
     * @param Definition $definition Module definition fetched or generated of composer file.
     * @return Installer
     */
    public static function getInstaller(Definition $definition)
    {
        return Installer::make(array('moduleDirectory' => $definition->getLocation()));
    }
}