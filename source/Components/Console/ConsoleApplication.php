<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Console;

use Spiral\Core\Core;
use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{
    /**
     * Constructor.
     *
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct("Spiral Framework Console Toolkit", Core::VERSION);
    }
}