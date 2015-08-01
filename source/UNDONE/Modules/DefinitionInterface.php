<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

interface DefinitionInterface
{
    /**
     * Module name, can contain version or other short description, like "Spiral Profiler v1.2.0"
     * Can be fetched from composer.json, "name" field.
     *
     * @return string
     */
    public function getName();

    /**
     * Module description, can be automatically fetched from composer.json file, "description" field.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Module class name used to fetch installer and register module in configuration.
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
     * namespaces and declaring that bootstrap() call is required. Installer declaration should be
     * located in Module::getInstaller() method.
     *
     * @return InstallerInterface
     */
    public function getInstaller();
}