<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Console;

use Spiral\Core\ContainerInterface;
use Spiral\Core\Core;
use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{
    /**
     * @var null|ContainerInterface
     */
    protected $container = null;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param string             $name    The name of the application
     * @param string             $version The version of the application
     */
    public function __construct(ContainerInterface $container, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->container = $container;
        parent::__construct("Spiral Framework Console Toolkit", Core::VERSION);
    }

    /**
     * Get associated container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}