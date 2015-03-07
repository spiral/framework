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
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Facade;

/**
 * @method static Database db(string $database = 'default', array $config = array(), Driver $driver = null)
 * @method static mixed interpolateQuery(string $query, array $parameters = array())
 * @method static string getAlias()
 * @method static DatabaseManager make(array $parameters = array())
 * @method static DatabaseManager getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static DispatcherInterface eventDispatcher(DispatcherInterface $dispatcher = null)
 */
class DBAL extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class name should be defined
     * in bindedComponent constant.
     */
    const COMPONENT = 'dbal';
}