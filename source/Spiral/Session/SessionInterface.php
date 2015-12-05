<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session;

use Spiral\Session\Exceptions\SessionException;

/**
 * Session store interface.
 */
interface SessionInterface
{
    /**
     * Set session id, if session already started data has to be committed to permanent storage.
     *
     * @param string $id
     * @param bool   $start Automatically start session with new id.
     * @throws SessionException
     */
    public function setID($id, $start = true);

    /**
     * Get session ID or create new one if session not started.
     *
     * @param bool $start Automatically start session.
     * @return string|null
     * @throws SessionException
     */
    public function getID($start = true);

    /**
     * Initiate store and start session.
     *
     * @throws SessionException
     */
    public function start();

    /**
     * Check is session has been started.
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Check is session were destroyed.
     *
     * @return bool
     */
    public function isDestroyed();

    /**
     * Commit all session data to session handler, this will close session before script ends.
     * Session will be restarted on next call.
     *
     * @throws SessionException
     */
    public function commit();

    /**
     * Destroys session data, session has to be started at this moment.
     *
     * @throws SessionException
     */
    public function destroy();

    /**
     * All values stored in session.
     *
     * @return array
     * @throws SessionException
     */
    public function all();

    /**
     * Set data in session. Value will be immediately available via $_SESSION array.
     *
     * @param string $name
     * @param mixed  $value
     * @return mixed
     * @throws SessionException
     */
    public function set($name, $value);

    /**
     * Check if value presented in session.
     *
     * @param string $name
     * @return bool
     * @throws SessionException
     */
    public function has($name);

    /**
     * Get value stored in session.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     * @throws SessionException
     */
    public function get($name, $default = null);

    /**
     * Increment numeric value stored in cache. Must return incremented value.
     *
     * @param string $name
     * @param int    $delta How much to increment by. Set to 1 by default.
     * @return int
     * @throws SessionException
     */
    public function increment($name, $delta = 1);

    /**
     * Decrement numeric value stored in cache. Must return decremented value.
     *
     * @param string $name
     * @param int    $delta How much to decrement by. Set to 1 by default.
     * @return int
     * @throws SessionException
     */
    public function decrement($name, $delta = 1);

    /**
     * Delete data from session.
     *
     * @param string $name
     * @throws SessionException
     */
    public function delete($name);

    /**
     * Read item from session and delete it after.
     *
     * @param string $name
     * @return mixed
     * @throws SessionException
     */
    public function pull($name);
}