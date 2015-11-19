<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session;

use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Session\Exceptions\SessionException;

/**
 * Default implementation of StoreInterface, can map session data to specified SessionHandler. By
 * default spiral session uses native php sessions as backbone, it can be changed in future.
 */
class SessionStore extends Component implements
    SingletonInterface,
    SessionInterface,
    \ArrayAccess,
    \IteratorAggregate
{
    /**
     * Handler creation can be slow, we can benchmark it.
     */
    use  BenchmarkTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Configuration section.
     */
    const CONFIG = 'session';

    /**
     * Handler name tells store to user native php sessions.
     */
    const NATIVE_HANDLER = 'native';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var \SessionHandler
     */
    private $handler = null;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var bool
     */
    private $destroyed = false;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     */
    public function __construct(ConfiguratorInterface $configurator, ContainerInterface $container)
    {
        $this->config = $configurator->getConfig(static::CONFIG);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setID($id)
    {
        if ($this->id == $id) {
            return;
        }

        if ($this->started) {
            $this->commit();
            $this->id = $id;
            $this->start();
        }

        if ($this->destroyed) {
            $this->destroyed = false;
            $this->id = $id;
            $this->start();
        }

        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getID($start = true)
    {
        if (!$this->started && $start) {
            $this->start();
        }

        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @param \SessionHandler $handler Custom session handler.
     */
    public function start(\SessionHandler $handler = null)
    {
        if ($this->started) {
            return true;
        }

        //We don't need cookies
        ini_set('session.use_cookies', false);

        !empty($this->id) && session_id($this->id);

        if (empty($handler)) {
            $defaultHandler = $this->config['handler'];

            if ($defaultHandler != self::NATIVE_HANDLER) {
                $config = $this->config['handlers'][$this->config['handler']];

                $benchmark = $this->benchmark('handler', $this->config['handler']);
                $handler = $this->handler = $this->container->construct($config['class'], [
                    'options'  => $config,
                    'lifetime' => $this->config['lifetime']
                ]);
                $this->benchmark($benchmark);
            }
        }

        !empty($handler) && session_set_save_handler($handler, true);

        try {
            $benchmark = $this->benchmark('start');
            session_start();
            $this->benchmark($benchmark);

            $this->id = session_id();
            $this->started = true;
            $this->destroyed = false;
        } catch (\ErrorException $exception) {
            throw new SessionException($exception->getMessage(), $exception->getCode());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function isDestroyed()
    {
        return $this->destroyed;
    }

    /**
     * Regenerate session id.
     *
     * @param bool $destruct Destruct previous data.
     */
    public function regenerateID($destruct = false)
    {
        $benchmark = $this->benchmark('regenerateID');
        session_regenerate_id($destruct);
        $this->benchmark($benchmark);

        $this->id = session_id();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $benchmark = $this->benchmark('commit');
        $this->started && session_write_close();
        $this->benchmark($benchmark);

        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        if (empty($this->id)) {
            return;
        }

        $benchmark = $this->benchmark('destroy');
        $this->started && session_destroy();
        $this->benchmark($benchmark);

        $this->id = '';
        $this->destroyed = true;
        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $this->start();

        return $_SESSION;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->start();

        $_SESSION[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->start() && isset($_SESSION[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        $this->start();

        return array_key_exists($name, $_SESSION) ? $_SESSION[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($name, $delta = 1)
    {
        $this->set($name, $this->get($name) + $delta);

        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($name, $delta = 1)
    {
        $this->set($name, $this->get($name) - $delta);

        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        $this->start();
        unset($_SESSION[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function pull($name)
    {
        $value = $this->get($name);
        $this->delete($name);

        return $value;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->delete($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($_SESSION);
    }
}