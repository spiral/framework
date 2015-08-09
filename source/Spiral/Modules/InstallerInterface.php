<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

/**
 * IModule installer responsible for operations like copying resources, registering configs, view
 * namespaces and declaring that Module::bootstrap() call is required.
 */
interface InstallerInterface
{
    /**
     * Ways to resolve file conflicts happen while moving public module files to application webroot
     * directory, conflicts may happen only if target file was altered or just different than module
     * declaration.
     */
    const OVERWRITE = 1;
    const IGNORE    = 2;

    /**
     * Check if modules requires bootstrapping.
     *
     * @return bool
     */
    public function isBootstrappable();

    /**
     * Declared module bindings, must be compatible with active container instance and be serializable
     * into array.
     *
     * @return array
     */
    public function getBindings();

    /**
     * Perform module installation. This method must mount all public files, configs, migrations and
     * etc.
     *
     * @param int $conflicts Method to resolve file conflicts.
     * @throws ModuleException
     */
    public function install($conflicts = self::OVERWRITE);

    /**
     * Perform module update, method must udpate all module files, no configs or migrations must be
     * created/altered.
     *
     * @param int $conflicts
     */
    public function update($conflicts = self::OVERWRITE);
}