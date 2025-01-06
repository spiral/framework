<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\AuthScope;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Security\GuardInterface;

class AuthController
{
    public function __construct(private readonly AuthScope $auth)
    {
    }

    public function do(GuardInterface $guard)
    {
        if (!$guard->allows('do')) {
            throw new ControllerException("Unauthorized permission 'do'", ControllerException::FORBIDDEN);
        }

        return 'ok';
    }

    public function token(AuthContextInterface $authContext)
    {
        if ($authContext->getToken() instanceof \Spiral\Auth\TokenInterface) {
            return $authContext->getToken()->getID();
        }

        return 'none';
    }

    public function login(AuthContextInterface $authContext, TokenStorageInterface $tokenStorage)
    {
        $authContext->start(
            $tokenStorage->create(['userID' => 1])
        );

        return 'OK';
    }

    public function logout()
    {
        $this->auth->close();

        return 'closed';
    }

    public function token2()
    {
        if ($this->auth->getToken() instanceof \Spiral\Auth\TokenInterface) {
            return $this->auth->getToken()->getID();
        }

        return 'none';
    }

    public function token3()
    {
        return $this->auth->getToken()->getPayload();
    }

    public function login2(TokenStorageInterface $tokenStorage)
    {
        $this->auth->start(
            $tokenStorage->create(['userID' => 1])
        );

        return 'OK';
    }

    public function actor()
    {
        $actor = $this->auth->getActor();
        return $actor ? $actor->getName() : 'none';
    }
}
