<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

use Spiral\Session\Exceptions\SessionException;

/**
 * Direct api to php session functionality. With segments support. Automatically provides access to
 * _SESSION global variable and fixates session using client signature.
 *
 * Session will be automatically started upon first request.
 */
class Session implements SessionInterface
{
    /**
     * Signs every session with user specific hash, provides ability to fixate session.
     */
    const CLIENT_SIGNATURE = '_CLIENT_SIGNATURE';

    /**
     * Unique string to identify client. Signature is stored inside the session.
     *
     * @var string
     */
    private $clientSignature;

    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @param string      $clientSignature
     * @param string|null $id
     */
    public function __construct(string $clientSignature, string $id = null)
    {
        $this->clientSignature = $clientSignature;
        $this->id = $this->isValid($id) ? $id : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function resume()
    {
        if ($this->isActive()) {
            return;
        }

        if (!empty($this->id)) {
            session_id($this->id);
        }

        try {
            session_start();
        } catch (\Throwable $e) {
            throw new SessionException($e->getMessage(), $e->getCode(), $e);
        }

        //We got new session
        $this->id = session_id();
        $this->started = true;

        if (!isset($_SESSION[self::CLIENT_SIGNATURE])) {
            //Newly created session, let's sign it
            $_SESSION[self::CLIENT_SIGNATURE] = $this->clientSignature;
        } elseif (!hash_equals($_SESSION[self::CLIENT_SIGNATURE], $this->clientSignature)) {
            //Flushing session id
            session_abort();

            $this->id = null;
            $this->resume();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $start
     */
    public function getID(bool $start = false)
    {
        if ($start) {
            $this->resume();
        }

        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function regenerateID(bool $destruct = false): SessionInterface
    {
        $this->resume();

        session_regenerate_id($destruct);
        $this->id = session_id();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        session_write_close();
        $this->started = false;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(): bool
    {
        if (!$this->isActive() || empty($this->id)) {
            return false;
        }

        $this->resume();
        session_destroy();
        $this->id = null;
        $this->started = false;

        return true;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'id'        => $this->id,
            'signature' => $this->clientSignature,
            'active'    => $this->isActive(),
            'data'      => $this->isActive() ? $_SESSION : null
        ];
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function getSegment(string $name = null): SectionInterface
//    {
//        $this->start();
//
//        if (empty($name)) {
//            return new SessionSection($_SESSION);
//        } else {
//            return new SessionSection($_SESSION[$name]);
//        }
//    }

//    /**
//     * {@inheritdoc}
//     */
//    public function createInjection(\ReflectionClass $class, string $context = null)
//    {
//        return $this->getSegment($context);
//    }

    /**
     * Check if given session ID valid.
     *
     * @param string $id
     *
     * @return bool
     */
    private function isValid(string $id): bool
    {
        return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id);
    }
}