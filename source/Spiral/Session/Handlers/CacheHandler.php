<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session\Handlers;

use Spiral\Cache\StoreInterface;

/**
 * Stores session data in specified cache store.
 */
class CacheHandler implements \SessionHandlerInterface
{
    /**
     * @var StoreInterface
     */
    protected $store = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var int
     */
    protected $lifetime = 0;

    /**
     * @param StoreInterface $store
     * @param array          $options  Session handler options.
     * @param int            $lifetime Default session lifetime.
     */
    public function __construct(StoreInterface $store, array $options, $lifetime = 0)
    {
        $this->store = $store;
        $this->options = $options;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        $this->store->delete($this->options['prefix'] . $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        return $this->store->get($this->options['prefix'] . $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        return $this->store->set(
            $this->options['prefix'] . $session_id,
            $session_data,
            $this->lifetime
        );
    }
}