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
 * Module definition must provide generic module information such as location, class name, size and
 * etc. In additional every Definition should know how to create module Installer.
 */
interface DefinitionInterface
{
    /**
     * Module name, can contain version or other short description.
     *
     * @return string
     */
    public function getName();

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Module class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Location of module class, all module files should be located in same directory.
     *
     * @return string
     */
    public function getLocation();

    /**
     * Total module size in bytes (will calculate all module files including resources, views and
     * module class itself).
     *
     * @return int
     */
    public function getSize();

    /**
     * Get list of module dependencies.
     *
     * @return array
     */
    public function getDependencies();

    /**
     * Check if module class already registered in modules list (modules config file).
     *
     * @return array|bool
     */
    public function isInstalled();

    /**
     * Module installer responsible for operations like copying resources, registering configs, view
     * namespaces and declaring that Module::bootstrap() call is required.
     *
     * @return InstallerInterface
     */
    public function getInstaller();
}