<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Controller\Traits\AuthorizesTrait;
use Spiral\Core\Controller;

class AuthController extends Controller
{
    use AuthorizesTrait;

    public function doAction()
    {
        $this->authorize('do');
        return 'ok';
    }

    public function tokenAction(AuthContextInterface $authContext)
    {
        if ($authContext->getToken() !== null) {
            return $authContext->getToken()->getID();
        }

        return 'none';
    }

    public function loginAction(AuthContextInterface $authContext, TokenStorageInterface $tokenStorage)
    {
        $authContext->start(
            $tokenStorage->create(['userID' => 1])
        );

        return 'OK';
    }
}
