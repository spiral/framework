<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Session\Handlers;

use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Cache\CacheStore;

class CacheHandler implements \SessionHandlerInterface
{
    /**
     * Cache store should be used to store sessions.
     *
     * @var CacheStore
     */
    protected $store = '';

    /**
     * Default session lifetime.
     *
     * @var int
     */
    protected $lifetime = 0;

    /**
     * Prefix should be used for all session keys stored in cache.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * New session handler instance.
     * PHP >= 5.4.0
     *
     * @param array        $options  Session handler options.
     * @param int          $lifetime Default session lifetime.
     * @param CacheManager $cache
     */
    public function __construct(array $options, $lifetime = 0, CacheManager $cache = null)
    {
        $this->lifetime = $lifetime;
        $this->store = $cache->store(
            $options['store'] == 'default' ? null : $options['store']
        );

        $this->prefix = !empty($options['prefix']) ? ':' . $options['prefix'] : '';
    }

    /**
     * Close the session, the return value (usually TRUE on success, FALSE on failure). Note this
     * value is returned internally to PHP for processing.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session, The return value (usually TRUE on success, FALSE on failure). Note this
     * value is returned internally to PHP for processing.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param int $session_id The session ID being destroyed.
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->store->delete($this->prefix . $session_id);
    }

    /**
     * Cleanup old sessions. The return value (usually TRUE on success, FALSE on failure). Note this
     * value is returned internally to PHP for processing.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime Sessions that have not updated for the last maxlifetime seconds will
     *                         be removed.
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * Initialize session. The return value (usually TRUE on success, FALSE on failure). Note this
     * value is returned internally to PHP for processing.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path  The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool
     */
    public function open($save_path, $session_id)
    {
        return true;
    }

    /**
     * Read session data. Returns an encoded string of the read data. If nothing was read, it must
     * return an empty string. Note this value is returned internally to PHP for processing.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string
     */
    public function read($session_id)
    {
        return $this->store->get($this->prefix . $session_id);
    }

    /**
     * Write session data. The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id   The session id.
     * @param string $session_data The encoded session data. This data is the result of the PHP
     *                             internally encoding the $_SESSION superglobal to a serialized string
     *                             and passing it as this parameter. Please note sessions use an
     *                             alternative serialization method.
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        return $this->store->set($this->prefix . $session_id, $session_data, $this->lifetime);
    }
}