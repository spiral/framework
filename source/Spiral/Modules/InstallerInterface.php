<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

interface InstallerInterface
{
    /**
     * Methods to resolve file conflicts happen while moving public module files to application root
     * directory, conflict may happen only if target file was altered or just different than module
     * declaration (can actually happen if module got updated, should be fixed in future somehow).
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
     * Perform module installation. This method will mount all files, configs and etc.
     *
     * @param int $conflicts Method to resolve file conflicts.
     * @throws ModuleException
     */
    public function install($conflicts = self::OVERWRITE);

    /**
     * Perform module update, method will move all module files, no configs or migrations will be
     * created/altered.
     *
     * @param int $conflicts
     */
    public function update($conflicts = self::OVERWRITE);
}