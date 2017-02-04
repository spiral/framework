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
 * @see  https://www.owasp.org/index.php/Session_Management_Cheat_Sheet
 * @todo http://php.net/manual/en/function.session-create-id.php
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
            throw new SessionException("Unable to start session", $e->getCode(), $e);
        }

        //We got new session
        $this->id = session_id();
        $this->started = true;

        if (!isset($_SESSION[self::CLIENT_SIGNATURE])) {
            //Newly created session, let's sign it
            $_SESSION[self::CLIENT_SIGNATURE] = $this->clientSignature;
        } elseif (!hash_equals($_SESSION[self::CLIENT_SIGNATURE], $this->clientSignature)) {
            $this->resetSession();
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

        session_commit();

        if ($destruct) {
            $_SESSION = [];
        }

        $this->resume();

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
        $this->resume();
        session_destroy();

        $_SESSION = [];
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

    /**
     * To be called in cases when client does not supplied proper session signature.
     */
    protected function resetSession()
    {
        $this->getLogger()->alert(
            "Session and client signatures do not match, session id: {$this->id}"
        );

        //Generating new session ID but keep old session intact
        $this->regenerateID(true);

        //Flush session state for a current user
        $_SESSION = [self::CLIENT_SIGNATURE => $this->clientSignature];
    }
}