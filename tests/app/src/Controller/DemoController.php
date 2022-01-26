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
use Spiral\Domain\Annotation\Guarded;

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

    /**
     * @Guarded()
     * @return string
     */
    public function guardedButNoName()
    {
        return 'ok';
    }

    /**
     * @return string
     */
    #[Guarded()]
    public function guardedButNoNameAttribute()
    {
        return 'ok';
    }

    /**
     * @Guarded("do")
     * @return string
     */
    public function do()
    {
        return 'ok';
    }

    /**
     * @return string
     */
    #[Guarded(permission: 'do')]
    public function doAttribute()
    {
        return 'ok';
    }
}
