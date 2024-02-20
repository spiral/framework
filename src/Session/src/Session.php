<?php

declare(strict_types=1);

namespace Spiral\Session;

use Spiral\Core\Attribute\Scope;
use Spiral\Session\Exception\SessionException;

/**
 * Direct api to php session functionality with segmentation support. Automatically provides access
 * to _SESSION global variable and signs session with user signature.
 *
 * Session will be automatically started upon first request.
 *
 * @see  https://www.owasp.org/index.php/Session_Management_Cheat_Sheet
 */
#[Scope('http')]
final class Session implements SessionInterface
{
    /**
     * Signs every session with user specific hash, provides ability to fixate session.
     */
    private const CLIENT_SIGNATURE = '_CLIENT_SIGNATURE';

    /**
     * Time when session been created or refreshed.
     */
    private const SESSION_CREATED = '_CREATED';

    /**
     * Locations for unnamed segments i.e. default segment.
     */
    private const DEFAULT_SECTION = '_DEFAULT';

    private ?string $id = null;
    private bool $started = false;

    /**
     * @param string $clientSignature Unique string to identify client. Signature is stored inside the session.
     * @param int $lifetime Session lifetime in seconds.
     */
    public function __construct(
        private readonly string $clientSignature,
        private readonly int $lifetime,
        string $id = null
    ) {
        if (!empty($id) && $this->validID($id)) {
            $this->id = $id;
        }
    }

    public function __debugInfo(): array
    {
        return [
            'id'        => $this->id,
            'signature' => $this->clientSignature,
            'started'   => $this->isStarted(),
            'data'      => $this->isStarted() ? $_SESSION : null,
        ];
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function resume(): void
    {
        if ($this->isStarted()) {
            return;
        }

        if (!empty($this->id)) {
            \session_id($this->id);
        } else {
            // always new id
            \session_id(\session_create_id());
        }

        try {
            \session_start(['use_cookies' => false]);
        } catch (\Throwable $e) {
            throw new SessionException('Unable to start session', (int) $e->getCode(), $e);
        }

        if (empty($this->id)) {
            //Sign newly created session
            $_SESSION[self::CLIENT_SIGNATURE] = $this->clientSignature;
            $_SESSION[self::SESSION_CREATED] = \time();
        }

        //We got new session
        $this->id = \session_id();
        $this->started = true;

        //Ensure that session is valid
        if (!$this->validSession()) {
            $this->invalidateSession();
        }
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    public function regenerateID(): SessionInterface
    {
        $this->resume();

        //Gaining new ID
        \session_regenerate_id();
        $this->id = \session_id();

        //Updating session duration
        $_SESSION[self::SESSION_CREATED] = \time();
        \session_write_close();

        //Restarting session under new ID
        $this->resume();

        return $this;
    }

    public function commit(): bool
    {
        if (!$this->isStarted()) {
            return false;
        }

        \session_write_close();
        $this->started = false;

        return true;
    }

    public function abort(): bool
    {
        if (!$this->isStarted()) {
            return false;
        }

        \session_abort();
        $this->started = false;

        return true;
    }

    public function destroy(): bool
    {
        $this->resume();
        $_SESSION = [
            self::CLIENT_SIGNATURE => $this->clientSignature,
            self::SESSION_CREATED  => \time(),
        ];

        return $this->commit();
    }

    public function getSection(string $name = null): SessionSectionInterface
    {
        return new SessionSection($this, $name ?? self::DEFAULT_SECTION);
    }

    /**
     * Check if session is valid for
     */
    protected function validSession(): bool
    {
        \assert(isset($_SESSION));

        if (
            !\array_key_exists(self::CLIENT_SIGNATURE, $_SESSION)
            || !\array_key_exists(self::SESSION_CREATED, $_SESSION)
        ) {
            //Missing session signature or timestamp!
            return false;
        }

        if ($_SESSION[self::SESSION_CREATED] < \time() - $this->lifetime) {
            //Session expired
            return false;
        }

        //Signatures do not match
        return \hash_equals($_SESSION[self::CLIENT_SIGNATURE], $this->clientSignature);
    }

    /**
     * To be called in cases when client does not supplied proper session signature.
     */
    protected function invalidateSession(): void
    {
        //Destroy all session data
        $this->destroy();

        //Switch user to new session
        $this->regenerateID();
    }

    /**
     * Check if given session ID valid.
     */
    private function validID(string $id): bool
    {
        return \preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id) !== false;
    }
}
