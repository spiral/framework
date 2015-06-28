<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Driver;
use Spiral\Components\DBAL\Migrations\Migrator;
use Spiral\Components\DBAL\Migrations\Repository;
use Spiral\Core\Container;
use Spiral\Core\Facade;

/**
 * @method static string defaultTimezone()
 * @method static Database db($database = 'default', array $config = array(), Driver $driver = null)
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter, Container $container)
 * @method static Repository migrationRepository($directory = null)
 * @method static Migrator getMigrator($database = 'default', $directory = null)
 * @method static mixed interpolateQuery($query, array $parameters = array())
 * @method static DatabaseManager make($parameters = array(), Container $container = null)
 * @method static DatabaseManager getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class DBAL extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'dbal';
}