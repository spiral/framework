<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Auth\Authenticators;

use Spiral\Auth\Exceptions\CredentialsException;
use Spiral\Auth\Exceptions\LogicException;
use Spiral\Auth\Hashing\PasswordHasher;
use Spiral\Auth\PasswordAwareInterface;
use Spiral\Auth\Sources\UsernameSourceInterface;

class CredentialsAuthenticator
{
    /**
     * @var UsernameSourceInterface
     */
    private $source;

    /**
     * @var PasswordHasher
     */
    private $hasher;

    /**
     * @param UsernameSourceInterface $source
     * @param PasswordHasher          $hasher
     */
    public function __construct(UsernameSourceInterface $source, PasswordHasher $hasher)
    {
        $this->source = $source;
        $this->hasher = $hasher;
    }

    /**
     * Authorizes user based on given credentials. Will throw CredentialsException in case of any
     * auth related error (i.e. invalid password, invalid username).
     *
     * @param string $username
     * @param string $password
     *
     * @return PasswordAwareInterface
     *
     * @throws LogicException When user does not implement proper interface.
     * @throws CredentialsException When user does not exists or credentials do not match.
     */
    public function getUser(string $username, string $password): PasswordAwareInterface
    {
        $user = $this->source->findByUsername($username);
        if (empty($user)) {
            throw new CredentialsException("Unable to authorize, no such user");
        }

        if (!$user instanceof PasswordAwareInterface) {
            throw new LogicException("User must be instance of PasswordAwareInterface");
        }

        if (!$this->hasher->hashEquals($password, $user->getPasswordHash())) {
            throw new CredentialsException("Unable to authorize, invalid password");
        }

        return $user;
    }
}