<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

use Spiral\Core\Component;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Session\Exceptions\SessionException;

/**
 * Direct api to php session functionality. With segments support. Automatically provides access to
 * _SESSION global variable and signs session with user signature.
 *
 * Session will be automatically started upon first request.
 *
 * @see https://www.owasp.org/index.php/Session_Management_Cheat_Sheet
 */
class Session extends Component implements SessionInterface
{
    use LoggerTrait;

    /**
     * Signs every session with user specific hash, provides ability to fixate session.
     */
    const CLIENT_SIGNATURE = '_CLIENT_SIGNATURE';

    /**
     * Locations for unnamed segments i.e. default segment.
     */
    const DEFAULT_SECTION = '_DEFAULT';

    /**
     * Unique string to identify client. Signature is stored inside the session.
     *
     * @var string
     */
    private $clientSignature;

    /**
     * @var string
     */
    private $id = null;

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

        if (!empty($id) && $this->validID($id)) {
            $this->id = $id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function resume()
    {
        if ($this->isStarted()) {
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
            $this->getLogger()->alert("Session and client signatures do not match, session id: {$this->id}");

            //Emptying session data
            $_SESSION = [];

            //Generating new session ID and flushing all existed data
            $this->regenerateID(false);

            $_SESSION[self::CLIENT_SIGNATURE] = $this->clientSignature;
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
        if (!$this->isStarted()) {
            $this->resume();
        }

        session_regenerate_id($destruct);
        $this->id = session_id();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        if (!$this->isStarted()) {
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
        if (!$this->isStarted() || empty($this->id)) {
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
            'active'    => $this->isStarted(),
            'data'      => $this->isStarted() ? $_SESSION : null
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(string $name = null): SessionSectionInterface
    {
        return new SessionSection($this, $name ?? static::DEFAULT_SECTION);
    }

    /**
     * {@inheritdoc}
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
    {
        return $this->getSection($context);
    }

    /**
     * Check if given session ID valid.
     *
     * @param string $id
     *
     * @return bool
     */
    private function validID(string $id): bool
    {
        return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id);
    }
}