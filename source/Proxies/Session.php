<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Components\Session\SessionStore;
use Spiral\Core\Container;
use Spiral\Core\StaticProxy;

/**
 * @method static void setID($id)
 * @method static string getID($start = true)
 * @method static bool isStarted()
 * @method static bool isDestroyed()
 * @method static \SessionHandler|null getHandler()
 * @method static bool start(\SessionHandler $handler = null)
 * @method static void regenerateID($deleteOld = false)
 * @method static void commit()
 * @method static void destroy()
 * @method static bool has($name)
 * @method static mixed get($name, $default = null)
 * @method static mixed set($name, $value)
 * @method static void delete($name)
 * @method static mixed pull($name)
 * @method static array all()
 * @method static boolean offsetExists($offset)
 * @method static mixed offsetGet($offset)
 * @method static void offsetSet($offset, $value)
 * @method static void offsetUnset($offset)
 * @method static \Traversable getIterator()
 * @method static SessionStore make($parameters = [], Container $container = null)
 * @method static SessionStore getInstance(Container $container = null)
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 * @method static void setLogger(LoggerInterface $logger)
 * @method static LoggerInterface|Logger logger()
 */
class Session extends StaticProxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'session';
}