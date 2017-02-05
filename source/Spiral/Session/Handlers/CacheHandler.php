<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session\Handlers;

use Psr\SimpleCache\CacheInterface;

class CacheHandler implements \SessionHandlerInterface
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param \Psr\SimpleCache\CacheInterface $cache
     * @param string                          $prefix
     */
    public function __construct(CacheInterface $cache, string $prefix = 'session-')
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
    }
}