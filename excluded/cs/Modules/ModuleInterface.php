<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Core\DirectoriesInterface;

/**
 * Every spiral module must declare two methods used to either edit existed configs (only once, at
 * moment of module installation) and publish module files like it's own configuration, images and
 * etc.
 */
interface ModuleInterface
{
    /**
     * Module must specify set of updates to be applied to existed configurations.
     *
     * @param RegistratorInterface $registrator
     */
    public function register(RegistratorInterface $registrator);

    /**
     * Module must publish set of files or directories.
     *
     * @param PublisherInterface   $publisher
     * @param DirectoriesInterface $directories
     */
    public function publish(PublisherInterface $publisher, DirectoriesInterface $directories);
}