<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\Session\SessionStore;
use Spiral\Core\Facade;

/**
 * @method static setID(string $id)
 * @method static string getID(bool $start = true)
 * @method static bool isStarted()
 * @method static bool isDestroyed()
 * @method static \SessionHandler getHandler()
 * @method static bool start(\SessionHandler $handler = null)
 * @method static regenerateID(bool $deleteOld = false)
 * @method static commit()
 * @method static destroy()
 * @method static bool has(string $name)
 * @method static mixed get(string $name, mixed $default = null)
 * @method static mixed set(string $name, mixed $value)
 * @method static delete(string $name)
 * @method static mixed pull(string $name)
 * @method static array getAll()
 * @method static boolean offsetExists(mixed $offset)
 * @method static mixed offsetGet(mixed $offset)
 * @method static void offsetSet(mixed $offset, mixed $value)
 * @method static void offsetUnset(mixed $offset)
 * @method static \Traversable getIterator()
 * @method static string getAlias()
 * @method static SessionStore make(array $parameters = array())
 * @method static SessionStore getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 */
class Session extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'session';
}