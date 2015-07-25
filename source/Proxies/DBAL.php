<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Driver;
use Spiral\Components\DBAL\Migrations\Migrator;
use Spiral\Components\DBAL\Migrations\Repository;
use Spiral\Core\Container;
use Spiral\Core\StaticProxy;

/**
 * DO NOT use StaticProxies!
 *
 * @method static string defaultTimezone()
 * @method static Database db($database = 'default', array $config = [], Driver $driver = null)
 * @method static mixed resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter, Container $container)
 * @method static Repository migrationRepository($directory = null)
 * @method static Migrator getMigrator($database = 'default', $directory = null)
 * @method static mixed interpolateQuery($query, array $parameters = [])
 * @method static DatabaseManager make($parameters = [], Container $container = null)
 * @method static DatabaseManager getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class DBAL extends StaticProxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'dbal';
}