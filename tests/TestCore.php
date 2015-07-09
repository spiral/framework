<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests;

use Spiral\Core\Container;
use Spiral\Core\Core;

class TestCore extends Core
{
    /**
     * Default set of core bindings. Can be redefined while constructing core.
     *
     * @invisible
     * @var array
     */
    protected $bindings = [];

    /**
     * Set of components to be pre-loaded before bootstrap method. By default spiral load Loader,
     * Modules and I18n components.
     *
     * @var array
     */
    protected $autoload = [];

    /**
     * Bootstrapping. Most of code responsible for routes, endpoints, events and other application
     * preparations should located in this method.
     */
    public function bootstrap()
    {
    }
}