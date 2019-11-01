<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\App\User\Role;
use Spiral\App\User\User;

class DemoController
{
    public function entity(User $user)
    {
        return $user->getName();
    }

    public function entity2(User $user, Role $role)
    {
        return 'ok';
    }
}
