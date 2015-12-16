<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session\Handlers;

use Spiral\Cache\CacheInterface;
use Spiral\Cache\StoreInterface;
use Spiral\Core\Component;
use Spiral\Core\Traits\SaturateTrait;

/**
 * Stores session data in specified cache store.
 */
class CacheHandler extends Component implements \SessionHandlerInterface
{
    /**
     * Additional constructor arguments.
     */
    use SaturateTrait;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var StoreInterface
     */
    protected $cache = null;

    /**
     * @var int
     */
    protected $lifetime = 0;

    /**
     * @param array          $options  Session handler options.
     * @param int            $lifetime Default session lifetime.
     * @param CacheInterface $cache
     */
    public function __construct(array $options, $lifetime = 0, CacheInterface $cache = null)
    {
        $this->lifetime = $lifetime;
        $this->options = $options;

        $this->cache = $this->saturate($cache, CacheInterface::class)->store(
            $this->options['store']
        );
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
        $this->cache->delete($this->options['prefix'] . $session_id);
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
        return $this->cache->get($this->options['prefix'] . $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        return $this->cache->set(
            $this->options['prefix'] . $session_id,
            $session_data,
            $this->lifetime
        );
    }
}